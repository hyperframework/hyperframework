<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Registry;
use Hyperframework\Common\Config;

class Response {
    /**
     * @param string $string
     * @param bool $shouldReplace
     * @param int $responseCode
     * @return void
     */
    public static function setHeader(
        $string, $shouldReplace = true, $responseCode = null
    ) {
        static::getEngine()->setHeader($string, $shouldReplace, $responseCode);
    }

    /**
     * @return string[]
     */
    public static function getHeaders() {
        return static::getEngine()->getHeaders();
    }

    /**
     * @param string $name
     * @return void
     */
    public static function removeHeader($name) {
        static::getEngine()->removeHeader($name);
    }

    /**
     * @return void
     */
    public static function removeHeaders() {
        static::getEngine()->removeHeaders();
    }

    /**
     * @param int $statusCode
     * @return void
     */
    public static function setStatusCode($statusCode) {
        static::getEngine()->setStatusCode($statusCode);
    }

    /**
     * @return int
     */
    public static function getStatusCode() {
        return static::getEngine()->getStatusCode();
    }

    /**
     * @param string $name
     * @param string $value
     * @param array $options
     * @return void
     */
    public static function setCookie($name, $value, $options = []) {
        static::getEngine()->setCookie($name, $value, $options);
    }

    /**
     * @return bool
     */
    public static function headersSent() {
        return static::getEngine()->headersSent();
    }

    /**
     * @return ResponseEngine
     */
    public static function getEngine() {
        return Registry::get('hyperframework.web.response_engine', function() {
            $class = Config::getClass(
                'hyperframework.web.response_engine_class',
                ResponseEngine::class
            );
            return new $class;
        });
    }
}
