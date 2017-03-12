<?php
namespace Hyperframework\Web;

use Exception;

class ServiceUnavailableException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct($message, 503, 'Service Unavailable', $previous);
    }
}
