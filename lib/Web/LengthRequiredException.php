<?php
namespace Hyperframework\Web;

use Exception;

class LengthRequiredHttpException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct($message, 411, 'Length Required', $previous);
    }
}
