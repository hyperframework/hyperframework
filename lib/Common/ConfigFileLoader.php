<?php
namespace Hyperframework\Common;

class ConfigFileLoader extends FileLoader {
    /**
     * @param string $path
     * @return string
     */
    protected static function getFullPath($path) {
        return ConfigFileFullPathBuilder::build($path);
    }
}
