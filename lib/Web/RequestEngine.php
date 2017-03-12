<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;

class RequestEngine {
    private $method;
    private $path;
    private $headers;
    private $body;
    private $isBodyInitialized = false;

    /**
     * @return string
     */
    public function getMethod() {
        if ($this->method !== null) {
            return $this->method;
        }
        $overriddenMethod = $this->getHeader('X_HTTP_METHOD_OVERRIDE');
        if ($overriddenMethod !== null) {
            $this->method = strtoupper($overriddenMethod);
        } else {
            $overriddenMethod = $this->getBodyParam('_method');
            if ($overriddenMethod !== null) {
                $this->method = strtoupper($overriddenMethod);
            }
        }
        if ($this->method === null) {
            $this->method = $_SERVER['REQUEST_METHOD'];
        } else {
            if (ctype_upper($this->method) === false) {
                throw new BadRequestException(
                    'The overridden request method is invalid.'
                );
            }
        }
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath() {
        if ($this->path === null) {
            $path = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
            if ($path === '') {
                $path = '/';
            } elseif (strpos($path, '//') !== false) {
                $path = preg_replace('#/{2,}#', '/', $path);
            }
            $this->path = '/' . trim($path, '/');
        }
        return $this->path;
    }

    /**
     * @return string
     */
    public function getDomain() {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getHeader($name, $default = null) {
        $headers = $this->getHeaders();
        return isset($headers[$name]) ? $headers[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name) {
        return isset($this->getHeaders()[$name]);
    }

    /**
     * @return string[]
     */
    public function getHeaders() {
        if ($this->headers !== null) {
            return $this->headers;
        }
        $this->headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $name => $value) {
                $name = strtoupper(str_replace('-', '_', $name));
                $this->headers[$name] = $value;
            }
        } else {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) === 'HTTP_') {
                    $name = substr($name, 5);
                    $this->headers[$name] = $value;
                }
            }
        }
        return $this->headers;
    }

    /**
     * @return resource
     */
    public function openInputStream() {
        return fopen('php://input', 'r');
    }

    /**
     * @return mixed
     */
    public function getBody() {
        if ($this->isBodyInitialized === false) {
            $this->initializeBody();
            $this->isBodyInitialized = true;
        }
        return $this->body;
    }

    /**
     * @param mixed $body
     * @return void
     */
    public function setBody($body) {
        $this->body = $body;
        $this->isBodyInitialized = true;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getQueryParam($name, $default = null) {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasQueryParam($name) {
        return isset($_GET[$name]);
    }

    /**
     * @return array
     */
    public function getQueryParams() {
        return $_GET;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getBodyParam($name, $default = null) {
        $params = $this->getBodyParams();
        return isset($params[$name]) ? $params[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasBodyParam($name) {
        return isset($this->getBodyParams()[$name]);
    }

    /**
     * @return array
     */
    public function getBodyParams() {
        $body = $this->getBody();
        return is_array($body) ? $body : [];
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getCookieParam($name, $default = null) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasCookieParam($name) {
        return isset($_COOKIE[$name]);
    }

    /**
     * @return array
     */
    public function getCookieParams() {
        return $_COOKIE;
    }

    /**
     * @return void
     */
    private function initializeBody() {
        $contentType = $this->getHeader('CONTENT_TYPE');
        if ($contentType === null) {
            $contentType = Config::getString(
                'hyperframework.web.default_request_content_type'
            );
        }
        if ($contentType !== null) {
            $contentType = strtolower(trim(
                explode(';', $contentType, 2)[0]
            ));
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($contentType === 'application/x-www-form-urlencoded'
                    || $contentType === 'multipart/form-data'
                ) {
                    $this->body = $_POST;
                    return;
                }
            }
            $this->body = $this->getRawBody();
            if ($contentType === 'application/x-www-form-urlencoded') {
                parse_str($this->body, $this->body);
            } elseif ($contentType === 'application/json') {
                $this->body = json_decode(
                    $this->body, true, 512, JSON_BIGINT_AS_STRING
                );
                if ($this->body === null) {
                    throw new BadRequestException(
                        'The request body is not a valid json.'
                    );
                }
            }
        } else {
            $this->body = $this->getRawBody();
        }
    }

    /**
     * @return string
     */
    private function getRawBody() {
        return stream_get_contents($this->openInputStream());
    }
}
