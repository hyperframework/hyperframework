<?php
namespace Hyperframework\Cli;

use Hyperframework\Cli\Test\TestCase as Base;

class MutuallyExclusiveOptionGroupConfigParserTest extends Base {
    private $optionConfigs;

    protected function setUp() {
        parent::setUp();
        $this->optionConfigs = [
            't' => new OptionConfig('test', 't', true, true, null, ''),
            't2' => new OptionConfig('test', 't', true, true, null, ''),
        ];
    }

    public function testParse() {
        $result = (new MutuallyExclusiveOptionGroupConfigParser)->parse(
            [['t', 't2', 'required' => true]], $this->optionConfigs
        );
        $result = $result[0];
        $this->assertTrue($result->isRequired());
        $this->assertSame(
            [$this->optionConfigs['t'], $this->optionConfigs['t2']],
            $result->getOptionConfigs()
        );
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidConfigs() {
        (new MutuallyExclusiveOptionGroupConfigParser)->parse(['config'], []);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidRequiredType() {
        (new MutuallyExclusiveOptionGroupConfigParser)->parse(
            [['required' => '']], []);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidOptionType() {
        (new MutuallyExclusiveOptionGroupConfigParser)->parse([[false]], []);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testOptionNotDefined() {
        (new MutuallyExclusiveOptionGroupConfigParser)->parse([['t']], []);
    }

    public function testOptionDeclearedMultipleTimes() {
        $result = (new MutuallyExclusiveOptionGroupConfigParser)->parse(
            [['t', 't']], $this->optionConfigs
        );
        $result = $result[0];
        $this->assertSame(
            [$this->optionConfigs['t']], $result->getOptionConfigs()
        );
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testOptionBelongsToMultipleGroups() {
        $result = (new MutuallyExclusiveOptionGroupConfigParser)->parse(
            [['t'], ['t']], $this->optionConfigs
        );
    }
}
