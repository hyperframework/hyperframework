<?php
namespace Hyperframework\Web\Test;

use Hyperframework\Common\Registry;
use Hyperframework\Web\App as Base;

class App extends Base {
    protected static function createApp() {
        return Registry::get('hyperframework.web.test.app');
    }
}
