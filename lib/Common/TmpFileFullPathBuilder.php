<?php
namespace Hyperframework\Common;

class TmpFileFullPathBuilder extends FileFullPathBuilder {
    /**
     * @return string
     */
    protected static function getRootPath() {
        return Config::getAppRootPath() . DIRECTORY_SEPARATOR . 'tmp';
    }
}
