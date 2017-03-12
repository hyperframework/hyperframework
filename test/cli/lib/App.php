<?php
namespace Hyperframework\Cli\Test;

use Hyperframework\Common\Registry;
use Hyperframework\Cli\App as Base;

class App extends Base {
    protected static function createApp($appRootPath) {
        return Registry::get('hyperframework.cli.test.app');
    }
}
