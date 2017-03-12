<?php
namespace Hyperframework\Web;

use Exception;

class InternalServerErrorException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct($message, 500, 'Internal Server Error', $previous);
    }
}
