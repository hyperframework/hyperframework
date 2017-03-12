<?php
namespace Hyperframework\Logging;

class LogLevel {
    const OFF     = 0;
    const FATAL   = 1;
    const ERROR   = 2;
    const WARNING = 3;
    const NOTICE  = 4;
    const INFO    = 5;
    const DEBUG   = 6;

    private static $levels = [
        'OFF'     => 0,
        'FATAL'   => 1,
        'ERROR'   => 2,
        'WARNING' => 3,
        'NOTICE'  => 4,
        'INFO'    => 5,
        'DEBUG'   => 6
    ];

    /**
     * @param string $name
     * @return int
     */
    public static function getCode($name) {
        if (isset(self::$levels[$name]) === false) {
            $name = strtoupper($name);
            if (isset(self::$levels[$name]) === false) {
                return;
            }
        }
        return self::$levels[$name];
    }

    /**
     * @param int $code
     * @return string
     */
    public static function getName($code) {
        $name = array_search($code, self::$levels, true);
        if ($name === false) {
            return null;
        }
        return $name;
    }
}
