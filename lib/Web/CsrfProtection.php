<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;

class CsrfProtection {
    /**
     * @return bool
     */
    public static function isEnabled() {
        return Config::getBool(
            'hyperframework.web.csrf_protection.enable', true
        );
    }

    /**
     * @return void
     */
    public static function run($shouldCheckTokenOfUnsafeMethods = true) {
        static::getEngine()->run($shouldCheckTokenOfUnsafeMethods);
    }

    /**
     * @return string
     */
    public static function getToken() {
        return static::getEngine()->getToken();
    }

    /**
     * @return string
     */
    public static function getTokenName() {
        return static::getEngine()->getTokenName();
    }

    /**
     * @return CsrfProtectionEngine
     */
    public static function getEngine() {
        return Registry::get(
            'hyperframework.web.csrf_protection.engine',
            function() {
                $class = Config::getClass(
                    'hyperframework.web.csrf_protection.engine_class',
                    CsrfProtectionEngine::class
                );
                return new $class;
            }
        );
    }
}
