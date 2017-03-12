<?php
namespace Hyperframework\Web;

use Exception;

class NotFoundException extends HttpException {
    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = null, $previous = null) {
        parent::__construct($message, 404, 'Not Found', $previous);
    }
}
