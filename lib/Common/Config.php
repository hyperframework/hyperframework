<?php
namespace Hyperframework\Common;

class Config {
    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get($name, $default = null) {
        return static::getEngine()->get($name, $default);
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public static function getString($name, $default = null) {
        return static::getEngine()->getString($name, $default);
    }

    /**
     * @param string $name
     * @param bool $default
     * @return bool
     */
    public static function getBool($name, $default = null) {
        return static::getEngine()->getBool($name, $default);
    }

    /**
     * @param string $name
     * @param int $default
     * @return int
     */
    public static function getInt($name, $default = null) {
        return static::getEngine()->getInt($name, $default);
    }

    /**
     * @param string $name
     * @param float $default
     * @return float
     */
    public static function getFloat($name, $default = null) {
        return static::getEngine()->getFloat($name, $default);
    }

    /**
     * @param string $name
     * @param array $default
     * @return array
     */
    public static function getArray($name, $default = null) {
        return static::getEngine()->getArray($name, $default);
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public static function getClass($name, $default = null) {
        return static::getEngine()->getClass($name, $default);
    }

    /**
     * @param string $name
     * @param callable $default
     * @return callable
     */
    public static function getCallable($name, $default = null) {
        return static::getEngine()->getCallable($name, $default);
    }

    /**
     * @return string
     */
    public static function getAppRootPath() {
        return static::getEngine()->getAppRootPath();
    }

    /**
     * @return string
     */
    public static function getAppRootNamespace() {
        return static::getEngine()->getAppRootNamespace();
    }

    /**
     * @return array
     */
    public static function getAll() {
        return static::getEngine()->getAll();
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function set($name, $value) {
        static::getEngine()->set($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has($name) {
        return static::getEngine()->has($name);
    }

    /**
     * @param string $name
     * @return void
     */
    public static function remove($name) {
        static::getEngine()->remove($name);
    }

    /**
     * @param array $data
     * @return void
     */
    public static function import($data) {
        static::getEngine()->import($data);
    }

    /**
     * @param string $path
     * @return void
     */
    public static function importFile($path) {
        static::getEngine()->importFile($path);
    }

    /**
     * @return ConfigEngine
     */
    public static function getEngine() {
        return Registry::get('hyperframework.config_engine', function() {
            return new ConfigEngine;
        });
    }

    /**
     * @param ConfigEngine $engine
     * @return void
     */
    public static function setEngine($engine) {
        Registry::set('hyperframework.config_engine', $engine);
    }
}
