<?php
namespace Hyperframework\Web;

use Exception;

class BadGatewayException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct($message, 502, 'Bad Gateway', $previous);
    }
}
