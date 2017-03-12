<?php
namespace Hyperframework\Common;

use Hyperframework\Logging\Logger;

class ErrorLogger extends Logger {
    /**
     * @return string
     */
    public static function getName() {
        return 'hyperframework.error_logger';
    }
}
