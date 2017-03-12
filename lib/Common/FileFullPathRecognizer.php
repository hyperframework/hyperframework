<?php
namespace Hyperframework\Common;

class FileFullPathRecognizer {
    /**
     * @param string $path
     * @return bool
     */
    public static function isFullPath($path) {
        $path = (string)$path;
        if ($path === '') {
            return false;
        }
        if (DIRECTORY_SEPARATOR === '/') {
            return $path[0] === '/';
        }
        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }
        if (isset($path[1]) === false) {
            return false;
        }
        if ($path[1] === ':') {
            return true;
        }
        return false;
    }
}
