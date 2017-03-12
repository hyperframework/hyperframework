<?php
namespace Hyperframework\Web;

use Exception;

class PreconditionFailedException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct($message, 412, 'Precondition Failed', $previous);
    }
}
