<?php
namespace Hyperframework\Cli;

use Hyperframework\Cli\Test\TestCase as Base;

class OptionArgumentConfigTest extends Base {
    public function test() {
        $config = new OptionArgumentConfig('name', false, []);
        $this->assertSame('name', $config->getName());
        $this->assertFalse($config->isRequired());
        $this->assertSame([], $config->getValues());
    }
}
