<?php
namespace Hyperframework\Cli;

use Hyperframework\Cli\Test\TestCase as Base;

class ArgumentConfigTest extends Base {
    public function test() {
        $config = new ArgumentConfig('name', false, true);
        $this->assertSame('name', $config->getName());
        $this->assertFalse($config->isRequired());
        $this->assertTrue($config->isRepeatable());
    }
}
