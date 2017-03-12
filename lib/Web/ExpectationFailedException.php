<?php
namespace Hyperframework\Web;

use Exception;

class ExpectationFailedException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct($message, 417, 'Expectation Failed', $previous);
    }
}
