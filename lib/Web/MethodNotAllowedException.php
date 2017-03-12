<?php
namespace Hyperframework\Web;

use Exception;

class MethodNotAllowedException extends HttpException {
    private $allowedMethods;

    /**
     * @param string[] $allowedMethods
     * @param string $message
     * @param Exception $previous
     */
    public function __construct(
        $allowedMethods, $message = null, $previous = null
    ) {
        parent::__construct($message, 405, 'Method Not Allowed', $previous);
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @return array
     */
    public function getHttpHeaders() {
        $headers = parent::getHttpHeaders();
        if (count($this->allowedMethods) !== 0) {
            $headers[] = 'Allow: ' . implode(', ', $this->allowedMethods);
        }
        return $headers;
    }
}
