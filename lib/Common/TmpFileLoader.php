<?php
namespace Hyperframework\Common;

class TmpFileLoader extends FileLoader {
    /**
     * @param string $path
     * @return string
     */
    protected static function getFullPath($path) {
        return TmpFileFullPathBuilder::build($path);
    }
}
