<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Registry;
use Hyperframework\Common\Config;

class Request {
    /**
     * @return string
     */
    public static function getMethod() {
        return static::getEngine()->getMethod();
    }

    /**
     * @return string
     */
    public static function getPath() {
        return static::getEngine()->getPath();
    }

    /**
     * @return string
     */
    public static function getDomain() {
        return static::getEngine()->getDomain();
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public static function getHeader($name, $default = null) {
        return static::getEngine()->getHeader($name, $default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function hasHeader($name) {
        return static::getEngine()->hasHeader($name);
    }

    /**
     * @return string[]
     */
    public static function getHeaders() {
        return static::getEngine()->getHeaders();
    }

    /**
     * @return resource
     */
    public static function openInputStream() {
        return static::getEngine()->openInputStream();
    }

    /**
     * @return string
     */
    public static function getBody() {
        return static::getEngine()->getBody();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getQueryParam($name, $default = null) {
        return static::getEngine()->getQueryParam($name, $default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function hasQueryParam($name) {
        return static::getEngine()->hasQueryParam($name);
    }

    /**
     * @return array
     */
    public static function getQueryParams() {
        return static::getEngine()->getQueryParams();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getBodyParam($name, $default = null) {
        return static::getEngine()->getBodyParam($name, $default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function hasBodyParam($name) {
        return static::getEngine()->hasBodyParam($name);
    }

    /**
     * @return array
     */
    public static function getBodyParams() {
        return static::getEngine()->getBodyParams();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getCookieParam($name, $default = null) {
        return static::getEngine()->getCookieParam($name, $default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function hasCookieParam($name) {
        return static::getEngine()->hasCookieParam($name);
    }

    /**
     * @return array
     */
    public static function getCookieParams() {
        return static::getEngine()->getCookieParams();
    }

    /**
     * @return RequestEngine
     */
    public static function getEngine() {
        return Registry::get('hyperframework.web.request_engine', function() {
            $class = Config::getClass(
                'hyperframework.web.request_engine_class', RequestEngine::class
            );
            return new $class;
        });
    }

    /**
     * @param RequestEngine $engine
     * @return void
     */
    public static function setEngine($engine) {
        Registry::set('hyperframework.web.request_engine', $engine);
    }
}
