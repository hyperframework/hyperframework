<?php
namespace Hyperframework\Cli\Test;

use Hyperframework\Common\Config;
use Hyperframework\Test\TestCase as Base;

class TestCase extends Base {
    protected function setUp() {
        Config::set('hyperframework.app_root_path', dirname(__DIR__));
        Config::set(
            'hyperframework.app_root_namespace', 'Hyperframework\Cli\Test'
        );
    }
}
