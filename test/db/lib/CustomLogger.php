<?php
namespace Hyperframework\Db\Test;

class CustomLogger {
    private static $log; 

    public static function getLog() {
        return self::$log;
    }

    public static function debug($log) {
        self::$log = $log;
    }
}
