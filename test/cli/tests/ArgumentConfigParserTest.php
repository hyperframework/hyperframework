<?php
namespace Hyperframework\Cli;

use Hyperframework\Cli\Test\TestCase as Base;

class ArgumentConfigParserTest extends Base {
    public function testParse() {
        $result = (new ArgumentConfigParser)->parse(
            [['name' => 'arg', 'required' => false, 'repeatable' => true]]
        );
        $argumentConfig = $result[0];
        $this->assertSame('arg', $argumentConfig->getName());
        $this->assertFalse($argumentConfig->isRequired());
        $this->assertTrue($argumentConfig->isRepeatable());
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidConfigs() {
        (new ArgumentConfigParser)->parse(['config']);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testNameIsMissing() {
        (new ArgumentConfigParser)->parse([[]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidNameType() {
        (new ArgumentConfigParser)->parse([['name' => true]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidNameFormat() {
        (new ArgumentConfigParser)->parse([['name' => '']]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidRequiredType() {
        (new ArgumentConfigParser)->parse([['name' => 'arg', 'required' => '']]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidOptionalArgument() {
        (new ArgumentConfigParser)->parse([
            ['name' => 'arg', 'required' => false],
            ['name' => 'arg2']
        ]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidRepeatableType() {
        (new ArgumentConfigParser)->parse([
            ['name' => 'arg', 'repeatable' => '']
        ]);
    }
}
