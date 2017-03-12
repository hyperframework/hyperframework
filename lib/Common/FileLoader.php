<?php
namespace Hyperframework\Common;

class FileLoader {
    /**
     * @param string $path
     * @return mixed
     */
    public static function loadPhp($path) {
        $path = static::getFullPath($path);
        return include $path;
    }

    /**
     * @param string $path
     * @return string
     */
    public static function loadData($path) {
        $path = static::getFullPath($path);
        return file_get_contents($path);
    }

    /**
     * @param string $path
     * @return string
     */
    protected static function getFullPath($path) {
        return FileFullPathBuilder::build($path);
    }
}
