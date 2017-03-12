<?php
namespace Hyperframework\Common;

class ErrorTypeHelper {
    /**
     * @param int $type
     * @return string
     */
    public static function convertToString($type) {
        switch ($type) {
            case E_STRICT:            return 'Strict standards';
            case E_DEPRECATED:
            case E_USER_DEPRECATED:   return 'Deprecated';
            case E_NOTICE:
            case E_USER_NOTICE:       return 'Notice';
            case E_WARNING:
            case E_USER_WARNING:      return 'Warning';
            case E_COMPILE_WARNING:   return 'Compile warning';
            case E_CORE_WARNING:      return 'Core warning';
            case E_USER_ERROR:        return 'Error';
            case E_RECOVERABLE_ERROR: return 'Recoverable error';
            case E_COMPILE_ERROR:     return 'Compile error';
            case E_PARSE:             return 'Parse error';
            case E_ERROR:             return 'Fatal error';
            case E_CORE_ERROR:        return 'Core error';
        }
    }

    /**
     * @param int $type
     * @return string
     */
    public static function convertToConstantName($type) {
        switch ($type) {
            case E_STRICT:            return 'E_STRICT';
            case E_DEPRECATED:        return 'E_DEPRECATED';
            case E_USER_DEPRECATED:   return 'E_USER_DEPRECATED';
            case E_NOTICE:            return 'E_NOTICE';
            case E_ERROR:             return 'E_ERROR';
            case E_USER_NOTICE:       return 'E_USER_NOTICE';
            case E_USER_ERROR:        return 'E_USER_ERROR';
            case E_WARNING:           return 'E_WARNING';
            case E_USER_WARNING:      return 'E_USER_WARNING';
            case E_COMPILE_WARNING:   return 'E_COMPILE_WARNING';
            case E_CORE_WARNING:      return 'E_CORE_WARNING';
            case E_RECOVERABLE_ERROR: return 'E_RECOVERABLE_ERROR';
            case E_PARSE:             return 'E_PARSE';
            case E_COMPILE_ERROR:     return 'E_COMPILE_ERROR';
            case E_CORE_ERROR:        return 'E_CORE_ERROR';
        }
    }
}
