<?php
namespace Hyperframework\Web;

use Exception;

class RequestedRangeNotSatisfiableException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct(
            $message, 416, 'Requested Range Not Satisfiable', $previous
        );
    }
}
