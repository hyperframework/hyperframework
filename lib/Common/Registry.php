<?php
namespace Hyperframework\Common;

use Closure;

class Registry {
    private static $data = [];

    /**
     * @param string $name
     * @param Closure $creationCallback
     * @return mixed
     */
    public static function get($name, $creationCallback = null) {
        if (isset(self::$data[$name])) {
            return self::$data[$name];
        }
        if ($creationCallback !== null) {
            $result = $creationCallback();
            if ($result !== null) {
                self::$data[$name] = $result;
                return $result;
            }
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function set($name, $value) {
        self::$data[$name] = $value;
    }

    /**
     * @param string $name
     * @return void
     */
    public static function remove($name) {
        unset(self::$data[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has($name) {
        return isset(self::$data[$name]);
    }

    /**
     * @return void
     */
    public static function clear() {
        self::$data = [];
    }
}
