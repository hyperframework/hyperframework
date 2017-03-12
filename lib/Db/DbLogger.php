<?php
namespace Hyperframework\Db;

use Hyperframework\Logging\Logger;

class DbLogger extends Logger {
    /**
     * @return string
     */
    public static function getName() {
        return 'hyperframework.db.logger';
    }
}
