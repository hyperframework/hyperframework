<?php
namespace Hyperframework\Common\Test;

use Hyperframework\Common\Config;
use Hyperframework\Test\TestCase as Base;

class TestCase extends Base {
    protected function setUp() {
        Config::set('hyperframework.app_root_path', dirname(__DIR__));
    }
}
