<?php
namespace Hyperframework\Web;

use Exception;

class NotImplementedException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct($message, 501, 'Not Implemented', $previous);
    }
}
