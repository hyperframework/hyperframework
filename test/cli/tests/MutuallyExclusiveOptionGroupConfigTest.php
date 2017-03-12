<?php
namespace Hyperframework\Cli;

use Hyperframework\Cli\Test\TestCase as Base;

class MutuallyExclusiveOptionGroupConfigTest extends Base {
    public function test() {
        $config = new MutuallyExclusiveOptionGroupConfig([], false);
        $this->assertFalse($config->isRequired());
        $this->assertSame([], $config->getOptionConfigs());
    }
}
