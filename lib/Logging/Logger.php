<?php
namespace Hyperframework\Logging;

use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;

class Logger {
    /**
     * @param mixed $mixed
     * @return void
     */
    public static function debug($mixed) {
        static::log(LogLevel::DEBUG, $mixed);
    }

    /**
     * @param mixed $mixed
     * @return void
     */
    public static function info($mixed) {
        static::log(LogLevel::INFO, $mixed);
    }

    /**
     * @param mixed $mixed
     * @return void
     */
    public static function notice($mixed) {
        static::log(LogLevel::NOTICE, $mixed);
    }

    /**
     * @param mixed $mixed
     * @return void
     */
    public static function warn($mixed) {
        static::log(LogLevel::WARNING, $mixed);
    }

    /**
     * @param mixed $mixed
     * @return void
     */
    public static function error($mixed) {
        static::log(LogLevel::ERROR, $mixed);
    }

    /**
     * @param mixed $mixed
     * @return void
     */
    public static function fatal($mixed) {
        static::log(LogLevel::FATAL, $mixed);
    }

    /**
     * @param int $level
     * @param mixed $mixed
     * @return void
     */
    public static function log($level, $mixed) {
        static::getEngine()->log($level, $mixed);
    }

    /**
     * @param int $level
     * @return void
     */
    public static function setLevel($level) {
        static::getEngine()->setLevel($level);
    }

    /**
     * @return int
     */
    public static function getLevel() {
        return static::getEngine()->getLevel();
    }

    /**
     * @return string
     */
    public static function getName() {
        return 'hyperframework.logging.logger';
    }

    /**
     * @return LoggerEngine
     */
    public static function getEngine() {
        $name = static::getName();
        return Registry::get(
            $name . '.engine',
            function() use ($name) {
                $class = Config::getClass(
                    $name . '.engine_class', Config::getClass(
                        'hyperframework.logging.logger_engine.class',
                        LoggerEngine::class
                    )
                );
                return new $class($name);
            }
        );
    }

    /**
     * @param LoggerEngine $engine
     * @return void
     */
    public static function setEngine($engine) {
        Registry::set(static::getName() . '.engine', $engine);
    }
}
