<?php
namespace Hyperframework\Web;

use Exception;

class NotAcceptableException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct($message, 406, 'Not Acceptable', $previous);
    }
}
