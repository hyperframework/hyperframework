<?php
namespace Hyperframework\Logging\Test;

use Hyperframework\Common\Config;
use Hyperframework\Test\TestCase as Base;

class TestCase extends Base {
    protected function setUp() {
        parent::setUp();
        Config::set('hyperframework.app_root_path', dirname(__DIR__));
    }
}
