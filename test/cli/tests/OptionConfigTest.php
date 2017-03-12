<?php
namespace Hyperframework\Cli;

use Hyperframework\Cli\Test\TestCase as Base;

class OptionConfigTest extends Base {
    public function test() {
        $arguemntConfig = new OptionArgumentConfig('name', true);
        $config = new OptionConfig(
            'name', 's', true, false, $arguemntConfig, 'description'
        );
        $this->assertSame('name', $config->getName());
        $this->assertSame('s', $config->getShortName());
        $this->assertTrue($config->isRequired());
        $this->assertFalse($config->isRepeatable());
        $this->assertSame($arguemntConfig, $config->getArgumentConfig());
        $this->assertSame('description', $config->getDescription());
    }
}
