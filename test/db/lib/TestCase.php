<?php
namespace Hyperframework\Db\Test;

use Hyperframework\Common\Config;
use Hyperframework\Common\ConfigFileLoader;
use Hyperframework\Db\DbClient;
use Hyperframework\Test\TestCase as Base;

class TestCase extends Base {
    protected function setUp() {
        Config::set('hyperframework.app_root_path', dirname(__DIR__));
        DbClient::execute(ConfigFileLoader::loadData('init.sql'));
        DbClient::setEngine(null);
    }
}