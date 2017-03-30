<?php
namespace Hyperframework\Common;

use CURLFile;
use Closure;

class WebClient {
    const OPT_DATA           = 'OPT_DATA';
    const OPT_DATA_TYPE      = 'OPT_DATA_TYPE';
    const OPT_QUERY_PARAMS   = 'OPT_QUERY_PARAMS';
    const OPT_ASYNC_REQUESTS = 'OPT_ASYNC_REQUESTS';
    const OPT_ASYNC_REQUEST_COMPELETE_CALLBACK
                             = 'OPT_ASYNC_REQUEST_COMPELETE_CALLBACK';
    const OPT_ASYNC_REQUEST_FETCHING_CALLBACK
                             = 'OPT_ASYNC_REQUEST_FETCHING_CALLBACK';
    const OPT_ASYNC_REQUEST_FETCHING_INTERVAL
                             = 'OPT_ASYNC_REQUEST_FETCHING_INTERVAL';
    const OPT_ASYNC_MAX_CONCURRENT_REQUESTS
                             = 'OPT_ASYNC_MAX_CONCURRENT_REQUESTS';

    private $handle;
    private $options = [];
    private $requestOptions = [];
    private $responseHeaders;
    private $rawResponseHeaders;

    /**
     * @param array $asyncOptions
     * @return void
     */
    public static function sendAsyncRequests($asyncOptions) {
        $asyncHandle = curl_multi_init();
        if ($asyncHandle === false) {
            throw new WebClientAsyncException(
                'Failed to initialize the cURL multi handle.'
            );
        }
        $asyncPendingRequests = isset($asyncOptions[self::OPT_ASYNC_REQUESTS]) ?
            $asyncOptions[self::OPT_ASYNC_REQUESTS] : [];
        $asyncProcessingRequests = [];
        $asyncRequestFetchingTime = null;
        $hasPendingRequest = true;
        $isRunning = false;
        $maxConcurrentRequests = self::getAsyncMaxConcurrentRequests(
            $asyncOptions
        );
        for (;;) {
            if ($isRunning) {
                do {
                    $status = curl_multi_exec(
                        $asyncHandle, $runningHandles
                    );
                    if ($runningHandles === 0) {
                        $isRunning = false;
                    }
                } while ($status === CURLM_CALL_MULTI_PERFORM);
                if ($status !== CURLM_OK) {
                    $message = curl_multi_strerror($status);
                    throw new WebClientAsyncException($message, $status);
                }
                while ($info = curl_multi_info_read($asyncHandle)) {
                    $handleId = (int)$info['handle'];
                    $client = $asyncProcessingRequests[$handleId];
                    unset($asyncProcessingRequests[$handleId]);
                    $requestCompeleteCallback =
                        self::getAsyncRequestCompeleteCallback($asyncOptions);
                    if ($requestCompeleteCallback !== null) {
                        $error = null;
                        $result = false;
                        if ($info['result'] !== CURLE_OK) {
                            $error = [
                                'code' => $info['result'],
                                'message' => curl_error($info['handle']),
                            ];
                        } else {
                            $result = $client->processResponse(
                                curl_multi_getcontent($info['handle'])
                            );
                        }
                        call_user_func_array(
                            $requestCompeleteCallback,
                            [$client, $result, $error]
                        );
                        $client->finalizeRequest();
                    }
                    curl_multi_remove_handle($asyncHandle, $info['handle']);
                }
                if ($isRunning) {
                    $timeout = null;
                    if ($hasPendingRequest === false
                        || $maxConcurrentRequests === count(
                            $asyncProcessingRequests
                        )
                    ) {
                        $timeout = 31536000;
                    } else {
                        $timeout = self::getAsyncRequestFetchingInterval(
                            $asyncRequestFetchingTime, $asyncOptions
                        );
                    }
                    $status = curl_multi_select($asyncHandle, $timeout);
                    //https://bugs.php.net/bug.php?id=61141
                    if ($status === -1) {
                        usleep(100);
                    }
                }
            }
            if ($hasPendingRequest) {
                $processingRequestCount = count($asyncProcessingRequests);
                if ($processingRequestCount < $maxConcurrentRequests) {
                    $interval = self::getAsyncRequestFetchingInterval(
                        $asyncRequestFetchingTime, $asyncOptions
                    );
                    if ($interval <= 0) {
                        $asyncRequestFetchingTime = self::getTime();
                        $hasPendingRequest = self::fetchAsyncRequests(
                            $asyncHandle,
                            $asyncPendingRequests,
                            $asyncProcessingRequests,
                            $asyncOptions
                        );
                        $isRunning = count($asyncProcessingRequests) > 0;
                        $interval = self::getAsyncRequestFetchingInterval(
                            $asyncRequestFetchingTime, $asyncOptions
                        );
                    }
                    if ($isRunning === false && $interval > 0) {
                        usleep(bcmul($interval, 1000000, 0));
                    }
                }
            } else {
                if ($isRunning === false) {
                    break;
                }
            }
        }
    }

    /**
     * @param array $options
     */
    public function __construct($options = []) {
        $this->setOptions($this->getDefaultOptions());
        $this->setOptions($options);
    }

    /**
     * @param array $options
     * @return string
     */
    public function send($options = []) {
        $this->initializeRequest($options);
        $result = curl_exec($this->handle);
        if ($result === false) {
            throw new WebClientException(
                curl_error($this->handle), curl_errno($this->handle)
            );
        }
        $result = $this->processResponse($result);
        $this->finalizeRequest();
        return $result;
    }

    /**
     * @param string $url
     * @param array $options
     * @return string
     */
    public function get($url, $options = []) {
        return $this->sendHttpRequest('GET', $url, null, $options);
    }

    /**
     * @param string $url
     * @param string|array $data
     * @param array $options
     * @return string
     */
    public function post($url, $data = null, $options = []) {
        return $this->sendHttpRequest('POST', $url, $data, $options);
    }

    /**
     * @param string $url
     * @param string|array $data
     * @param array $options
     * @return string
     */
    public function patch($url, $data = null, $options = []) {
        return $this->sendHttpRequest('PATCH', $url, $data, $options);
    }

    /**
     * @param string $url
     * @param string|array $data
     * @param array $options
     * @return string
     */
    public function put($url, $data = null, $options = []) {
        return $this->sendHttpRequest('PUT', $url, $data, $options);
    }

    /**
     * @param string $url
     * @param array $options
     * @return string
     */
    public function delete($url, $options = []) {
        return $this->sendHttpRequest('DELETE', $url, null, $options);
    }

    /**
     * @param string $name
     * @param bool $isMultiple
     * @return string
     */
    public function getResponseHeader($name, $isMultiple = false) {
        $headers = $this->getResponseHeaders();
        if (isset($headers[$name])) {
            if (is_array($headers[$name])) {
                if ($isMultiple) {
                    return $headers[$name];
                } else {
                    return end($headers[$name]);
                }
            }
            if ($isMultiple) {
                return [$headers[$name]];
            }
            return $headers[$name];
        }
    }

    /**
     * @return array
     */
    public function getResponseHeaders() {
        if ($this->responseHeaders === null) {
            return [];
        }
        return $this->responseHeaders;
    }

    /**
     * @return string
     */
    public function getRawResponseHeaders() {
        return $this->rawResponseHeaders;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getResponseInfo($name = null) {
        if ($this->handle === null) {
            throw new WebClientException('No response.');
        }
        if ($name === null) {
            return curl_getinfo($this->handle);
        }
        return curl_getinfo($this->handle, $name);
    }

    /**
     * @param mixed $name
     * @return mixed
     */
    public function getOption($name) {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }
    }

    /**
     * @param mixed $name
     * @param mixed $value
     * @return void
     */
    public function setOption($name, $value) {
        $this->options[$name] = $value;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setOptions($options) {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param string $name
     * @return void
     */
    public function removeOption($name) {
        unset($this->options[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption($name) {
        return isset($this->options[$name]);
    }

    /**
     * @return void
     */
    public function resetOptions() {
        $this->options = $this->getDefaultOptions();
    }

    public function __clone() {
        $this->handle = null;
    }

    /**
     * @return array
     */
    protected function getDefaultOptions() {
        return [
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS      => 512,
            CURLOPT_AUTOREFERER    => 1,
            CURLOPT_ENCODING       => '',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER         => 1
        ];
    }

    /**
     * @return void
     */
    protected function processExtraRequestOptions() {
        $this->processDataRequestOption();
        $this->processQueryParamsRequestOption();
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getRequestOption($name) {
        if (isset($this->requestOptions[$name])) {
            return $this->requestOptions[$name];
        }
    }

    /**
     * @return array
     */
    protected function getRequestOptions() {
        return $this->requestOptions;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function setRequestOption($name, $value) {
        $this->requestOptions[$name] = $value;
    }

    /**
     * @param array $options
     * @return void
     */
    protected function setRequestOptions($options) {
        foreach ($options as $name => $value) {
            $this->setRequestOption($name, $value);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function hasRequestOption($name) {
        return isset($this->requestOptions[$name]);
    }

    /**
     * @param string $name
     * @return void
     */
    protected function removeRequestOption($name) {
        unset($this->requestOptions[$name]);
    }

    /**
     * @return void
     */
    protected function resetRequestOptions() {
        $this->requestOptions = [];
    }

    /**
     * @param string $header
     * @return void
     */
    protected function addRequestHeader($header) {
        if (isset($this->requestOptions[CURLOPT_HTTPHEADER]) === false) {
            $this->requestOptions[CURLOPT_HTTPHEADER] = [$header];
            return;
        }
        if (is_array($this->requestOptions[CURLOPT_HTTPHEADER]) === false) {
            $type = gettype($this->requestOptions[CURLOPT_HTTPHEADER]);
            throw new WebClientException(
                "cURL Option 'CURLOPT_HTTPHEADER' must be an array,"
                    . " $type given."
            );
        }
        $this->requestOptions[CURLOPT_HTTPHEADER][] = $header;
    }

    /**
     * @return string
     */
    private static function getTime() {
        list($usec, $sec) = explode(" ", microtime());
        return bcadd($usec, $sec, 6);
    }

    /**
     * @param resource $asyncHandle
     * @param array &$asyncPendingRequests
     * @param array &$asyncProcessingRequests
     * @param array $asyncOptions
     * @return bool
     */
    private static function fetchAsyncRequests(
        $asyncHandle,
        &$asyncPendingRequests,
        &$asyncProcessingRequests,
        $asyncOptions
    ) {
        $pendingRequestCount = count($asyncPendingRequests);
        $processingRequestCount = count($asyncProcessingRequests);
        $maxConcurrentRequests = self::getAsyncMaxConcurrentRequests(
            $asyncOptions
        );
        $requestFetchingCallback = self::getAsyncRequestFetchingCallback(
            $asyncOptions
        );
        $isFetchingCallbackInvoked = false;
        while ($processingRequestCount < $maxConcurrentRequests) {
            if ($pendingRequestCount === 0) {
                if ($isFetchingCallbackInvoked) {
                    return true;
                }
                if ($requestFetchingCallback !== null) {
                    $tmp = call_user_func($requestFetchingCallback);
                    if ($tmp === false) {
                        return false;
                    } elseif ($tmp === true || $tmp === null) {
                        return true;
                    } elseif (is_array($tmp) === false) {
                        $type = gettype($tmp);
                        throw new WebClientAsyncException(
                            "The return value of request fetching callback must"
                                . " be an array or a bool, $type given."
                        );
                    }
                    $pendingRequestCount = count($tmp);
                    if ($pendingRequestCount === 0) {
                        return true;
                    }
                    $asyncPendingRequests = $tmp;
                    $isFetchingCallbackInvoked = true;
                } else {
                    return false;
                }
            }
            $key = key($asyncPendingRequests);
            $client = $asyncPendingRequests[$key];
            unset($asyncPendingRequests[$key]);
            --$pendingRequestCount;
            ++$processingRequestCount;
            if (is_array($client)) {
                $tmp = new static;
                $tmp->setOptions($client);
                $client = $tmp;
            } elseif ($client instanceof WebClient === false) {
                $type = gettype($client);
                throw new WebClientAsyncException(
                    'The request must be an instance of '
                        . __CLASS__ . " or an option array, $type given."
                );
            }
            $client->initializeRequest();
            $index = (int)$client->handle;
            if (isset($asyncProcessingRequests[$index])) {
                throw new WebClientAsyncException(
                    'The web client already exists in the processing queue.'
                );
            }
            $asyncProcessingRequests[$index] = $client;
            $code = curl_multi_add_handle(
                $asyncHandle, $client->handle
            );
            if ($code !== CURLM_OK) {
                throw new WebClientAsyncException(
                    curl_multi_strerror($code), $code
                );
            }
        }
        return true;
    }

    /**
     * @param array $asyncOptions
     * @return int
     */
    private static function getAsyncMaxConcurrentRequests($asyncOptions) {
        $optionName = self::OPT_ASYNC_MAX_CONCURRENT_REQUESTS;
        if (isset($asyncOptions[$optionName])) {
            if ($asyncOptions[$optionName] < 1) {
                throw new WebClientAsyncException(
                    "The value of option '$optionName' is invalid."
                );
            }
            return $asyncOptions[$optionName];
        } else {
            return PHP_INT_MAX;
        }
    }

    /**
     * @param array $asyncOptions
     * @return callback 
     */
    private static function getAsyncRequestCompeleteCallback($asyncOptions) {
        $optionName = self::OPT_ASYNC_REQUEST_COMPELETE_CALLBACK;
        if (isset($asyncOptions[$optionName])) {
            $result = $asyncOptions[$optionName];
            if (is_callable($result) === false) {
                throw new WebClientAsyncException(
                    "The value of option '$optionName' is not callable."
                );
            }
            return $result;
        }
    }

    /**
     * @param array $asyncOptions
     * @return callback 
     */
    private static function getAsyncRequestFetchingCallback($asyncOptions) {
        $optionName = self::OPT_ASYNC_REQUEST_FETCHING_CALLBACK;
        if (isset($asyncOptions[$optionName])) {
            $result = $asyncOptions[$optionName];
            if (is_callable($result) === false) {
                throw new WebClientAsyncException(
                    "The value of option '$optionName' is not callable."
                );
            }
            return $result;
        }
    }

    /**
     * @param string $asyncRequestFetchingTime
     * @param array $asyncOptions
     * @return string
     */
    private static function getAsyncRequestFetchingInterval(
        $asyncRequestFetchingTime, $asyncOptions
    ) {
        if ($asyncRequestFetchingTime === null) {
            return '0';
        }
        $optionValue = 1;
        $optionName = self::OPT_ASYNC_REQUEST_FETCHING_INTERVAL;
        if (isset($asyncOptions[$optionName])) {
            $optionValue = $asyncOptions[$optionName];
            if ($optionValue < 0) {
                throw new WebClientAsyncException(
                    "The value of option '$optionName' is invalid."
                );
            }
        }
        return bcsub(
            $optionValue,
            bcsub(self::getTime(), $asyncRequestFetchingTime, 6),
            6
        );
    }

    /**
     * @param array $options
     * @return void
     */
    private function initializeRequest($options = []) {
        $this->responseHeaders = [];
        $this->rawResponseHeaders = null;
        $this->setRequestOptions(array_replace($this->getOptions(), $options));
        $this->processExtraRequestOptions();
        $this->initializeCurlCallbacks();
        $curlOptions = [];
        foreach ($this->getRequestOptions() as $optionName => $optionValue) {
            if (is_int($optionName)) {
                $curlOptions[$optionName] = $optionValue;
            }
        }
        if ($this->handle !== null) {
            curl_reset($this->handle);
        } else {
            $this->handle = curl_init();
            if ($this->handle === false) {
                throw new WebClientException(
                    'Failed to initialize the cURL handle.'
                );
            }
        }
        curl_setopt_array($this->handle, $curlOptions);
    }

    /**
     * @return void
     */
    private function finalizeRequest() {
        $this->resetRequestOptions();
    }

    /**
     * @return void
     */
    private function processDataRequestOption() {
        if ($this->hasRequestOption(self::OPT_DATA) === false) {
            return;
        }
        $data = $this->getRequestOption(self::OPT_DATA);
        $defaultType = 'application/json';
        $type = $this->hasRequestOption(self::OPT_DATA_TYPE) ? 
            $this->getRequestOption(self::OPT_DATA_TYPE) : $defaultType;
        $typeSuffix = null;
        $position = strpos($type, ';');
        if ($position !== false) {
            $typeSuffix = substr($type, $position);
            $type = substr($type, 0, $position);
        }
        $lowercaseType = strtolower(trim($type));
        if (is_string($data)) {
            $this->addRequestHeader('Content-Type: ' . $type . $typeSuffix);
            $this->initializeCurlPostFieldOptions();
            $this->setRequestOption(CURLOPT_POSTFIELDS, $data);
            return;
        }
        if ($lowercaseType === 'multipart/form-data') {
            if (is_array($data) === false) {
                $type = gettype($data);
                throw new WebClientException(
                    "The value of option 'OPT_DATA' is invalid, "
                        . "multipart form data must be an array, "
                        . "$type given."
                );
            }
            $boundary = 'BOUNDARY-' . sha1(uniqid(mt_rand(), true));
            $formData = [];
            $tmp = [];
            $this->formatFormDataNames($data, $tmp);
            foreach ($tmp as $key => $value) {
                $fileName = null;
                $contentType = null;
                if ($value instanceof CURLFile) {
                    $postFileName = (string)$value->getPostFilename();
                    if ($postFileName === '') {
                        $postFileName = basename($value->getFilename());
                    }
                    $fileName = '; filename="' . str_replace(
                        '"', '\"', $postFileName
                    ) . '"';
                    $mimeType = (string)$value->getMimeType();
                    if ($mimeType === '') {
                        $mimeType = 'application/octet-stream';
                    }
                    $contentType = "\r\nContent-Type: " . $mimeType;
                } else {
                    $value = (string)$value;
                }
                $formData[] = [
                    'headers' => '--' . $boundary . "\r\n"
                        . 'Content-Disposition: form-data; name="' . $key
                        . '"' . $fileName . $contentType . "\r\n\r\n",
                    'body' => $value
                ];
            }
            $size = $this->getFormDataSize($formData, $boundary);
            $this->addRequestHeader(
                'Content-Type: ' . $type . $typeSuffix
                    . '; boundary=' . $boundary
            );
            $this->addRequestHeader('Content-Length: ' . $size);
            $this->initializeCurlPostFieldOptions();
            $this->removeRequestOption(CURLOPT_POSTFIELDS);
            $this->setRequestOption(
                CURLOPT_READFUNCTION, $this->getSendFormDataCallback(
                    $formData, $boundary
                )
            );
        } elseif ($lowercaseType === 'application/x-www-form-urlencoded') {
            $this->addRequestHeader('Content-Type: ' . $type . $typeSuffix);
            $this->initializeCurlPostFieldOptions();
            $content = http_build_query($data, null, '&', PHP_QUERY_RFC3986);
            $this->setRequestOption(CURLOPT_POSTFIELDS, $content);
        } elseif ($lowercaseType === $defaultType) {
            $this->addRequestHeader('Content-Type: ' . $type . $typeSuffix);
            $this->initializeCurlPostFieldOptions();
            $this->setRequestOption(CURLOPT_POSTFIELDS, json_encode(
                $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));
        } else {
            throw new WebClientException(
                "Data type '$type' is not supported."
            );
        }
    }

    /**
     * @param array $data
     * @param array $result
     * @param string $prefix
     * @return void
     */
    private function formatFormDataNames($data, &$result, $prefix = '') {
        foreach ($data as $key => $value) {
            $key = str_replace('"', '\"', $key);
            if ($prefix !== '') {
                $key = $prefix . '[' . $key . ']';
            }
            if (is_array($value)) {
                $this->formatFormDataNames($value, $result, $key);
            } else {
                $result[$key] = $value;
            }
        }
    }

    /**
     * @param array $formData
     * @param string $boundary
     * @return Closure
     */
    private function getSendFormDataCallback($formData, $boundary) {
        $cache = null;
        $file = null;
        $fileName = null;
        $isStarted = false;
        $isFinished = false;
        $tail = '';
        return function($handle, $inFile, $maxLength) use (
            &$formData,
            &$cache,
            &$file,
            &$fileName,
            &$isStarted,
            &$isFinished,
            &$tail,
            $boundary
        ) {
            if ($isFinished) {
                return;
            }
            for (;;) {
                $cacheLength = strlen($cache);
                if ($cacheLength !== 0) {
                    if ($maxLength <= $cacheLength) {
                        $result = substr($cache, 0, $maxLength);
                        $cache = substr($cache, $maxLength);
                        return $result;
                    } else {
                        $result = $cache;
                        $cache = null;
                        return $result;
                    }
                }
                if ($file === null) {
                    if (count($formData) === 0) {
                        $isFinished = true;
                        return "\r\n--" . $boundary . "--\r\n";
                    }
                    $index = key($formData);
                    $value = $formData[$index];
                    $cache = null;
                    if ($isStarted) {
                        $cache = "\r\n";
                    } else {
                        $isStarted = true;
                    }
                    $cache .= $value['headers'];
                    if ($value['body'] instanceof CURLFile) {
                        $fileName = $value['body']->getFilename();
                        $file = fopen($fileName, 'r');
                        if ($file === false) {
                            throw new WebClientException(
                                "Failed to open file '$fileName'."
                            );
                        }
                    } else {
                        $cache .= $value['body'];
                    } 
                    unset($formData[$index]);
                } else {
                    $result = fgets($file, $maxLength);
                    if ($result === false) {
                        if (!feof($file)) {
                            throw new WebClientException(
                                "Failed to read file '$fileName'."
                            );
                        }
                        $result = '';
                    }
                    if (feof($file)) {
                        fclose($file);
                        $file = null;
                    }
                    if ($result !== '') {
                        $tmp = "--" . $boundary;
                        $tmpLength = strlen($tmp);
                        $tail .= $result;
                        if (strlen($tail) >= $tmpLength) {
                            if (strpos($tail, $tmp) !== false) {
                                throw new WebClientException(
                                    'Form data boundary error.'
                                );
                            }
                            $tail = substr($tail,  1 - $tmpLength);
                        }
                        return $result;
                    }
                }
            }
        };
    }

    /**
     * @param array $formData
     * @param string $boundary
     * @return int
     */
    private function getFormDataSize($formData, $boundary) {
        $result = 0;
        foreach ($formData as $item) {
            $result = bcadd(strlen($item['headers']), $result);
            if ($item['body'] instanceof CURLFile) {
                $fileName = $item['body']->getFilename();
                $fileSize = filesize($fileName);
                if ($fileSize === false) {
                    throw new WebClientException(
                        "Failed to get size of file '$fileName'."
                    );
                }
                $result = bcadd($fileSize, $result);
            } else {
                $result = bcadd(strlen($item['body']), $result);
            }
        }
        return bcadd(count($formData) * 2 + strlen($boundary) + 6, $result);
    }

    /**
     * @return void
     */
    private function initializeCurlPostFieldOptions() {
        $this->removeRequestOption(CURLOPT_UPLOAD);
        $this->removeRequestOption(CURLOPT_PUT);
        $this->setRequestOption(CURLOPT_POST, true);
    }

    /**
     * @return void
     */
    private function processQueryParamsRequestOption() {
        $queryParams = $this->getRequestOption(self::OPT_QUERY_PARAMS);
        $url = $this->getRequestOption(CURLOPT_URL);
        if ($queryParams === null || $url === null) {
            return;
        }
        if (is_array($queryParams) === false) {
            $type = gettype($queryParams);
            throw new WebClientException(
                "The value of option 'OPT_QUERY_PARAMS'"
                    . " must be an array, $type given."
            );
        }
        $questionMarkPosition = strpos($url, '?');
        if ($questionMarkPosition !== false) {
            $url = substr($url, 0, $questionMarkPosition);
        }
        $queryString = http_build_query(
            $queryParams, null, '&', PHP_QUERY_RFC3986
        );
        if ($queryString !== '') {
            $url .= '?' . $queryString;
        }
        $this->setRequestOption(CURLOPT_URL, $url);
    }

    /**
     * @return void
     */
    private function initializeCurlCallbacks() {
        foreach ($this->getRequestOptions() as $optionName => &$optionValue) {
            if ($optionName === CURLOPT_HEADERFUNCTION 
                || $optionName === CURLOPT_WRITEFUNCTION
            ) {
                $optionValue = function($handle, $arg1) use ($optionValue) {
                    return call_user_func_array($optionValue, [$this, $arg1]);
                };
            } elseif ($optionName === CURLOPT_READFUNCTION
                || (defined('CURLOPT_PASSWDFUNCTION')
                    && $optionName === CURLOPT_PASSWDFUNCTION
                )
            ) {
                $optionValue = function($handle, $arg1, $arg2) use (
                    $optionValue
                ) {
                    return call_user_func_array(
                        $optionValue, [$this, $arg1, $arg2]
                    );
                };
            } elseif ($optionName === CURLOPT_PROGRESSFUNCTION) {
                $optionValue = function($handle, $arg1, $arg2, $arg3, $arg4)
                    use ($optionValue)
                {
                    return call_user_func_array(
                        $optionValue, [$this, $arg1, $arg2, $arg3, $arg4]
                    );
                };
            }
        }
    }

    /**
     * @param string $result
     * @return string
     */
    private function processResponse($result) {
        if ((bool)$this->getRequestOption(CURLOPT_HEADER) === false) {
            return $result;
        }
        $headerSize = $this->getResponseInfo(CURLINFO_HEADER_SIZE);
        $this->rawResponseHeaders = substr($result, 0, $headerSize);
        if (strlen($result) > $headerSize) {
            $result = substr($result, $headerSize);
        } else {
            $result = '';
        }
        $url = $this->getResponseInfo(CURLINFO_EFFECTIVE_URL);
        $tmp = explode('://', $url, 2);
        $protocol = strtolower($tmp[0]);
        if ($protocol === 'http'
            || $protocol === 'https'
            || $protocol === 'file'
            || $protocol === 'ftp'
            || $protocol === 'sftp'
            || $protocol === 'ftps'
        ) {
            if ($headerSize === 0) {
                return $result;
            }
            $headers = explode("\r\n", trim($this->rawResponseHeaders));
            foreach ($headers as $header) {
                if ($header === '') {
                    $this->responseHeaders = [];
                }
                if (strpos($header, ':') === false) {
                    continue;
                }
                $tmp = explode(':', $header, 2);
                $name = $tmp[0];
                $value = null;
                if (isset($tmp[1])) {
                    $value = ltrim($tmp[1], ' ');
                }
                if (isset($this->responseHeaders[$name])) {
                    if (is_array($this->responseHeaders[$name]) === false) {
                        $this->responseHeaders[$name] =
                            [$this->responseHeaders[$name]];
                    }
                    $this->responseHeaders[$name][] = $value;
                } else {
                    $this->responseHeaders[$name] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param array $options
     * @return string
     */
    private function sendHttpRequest($method, $url, $data, $options = []) {
        $options[CURLOPT_CUSTOMREQUEST] = $method;
        $options[CURLOPT_URL] = $url;
        if ($data !== null) {
            $options[self::OPT_DATA] = $data;
        }
        return $this->send($options);
    }
}
