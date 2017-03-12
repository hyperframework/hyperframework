<?php
namespace Hyperframework\Cli;

use Hyperframework\Cli\Test\TestCase as Base;

class OptionConfigParserTest extends Base {
    public function testParse() {
        $result = (new OptionConfigParser)->parse([[
            'name' => 'test',
            'short_name' => 't',
            'repeatable' => true,
            'required' => true,
            'description' => 'description',
            'argument' => [
                'name' => 'arg',
                'required' => false,
                'values' => ['a', 'b'],
            ]
        ]]);
        $optionConfig = $result[0];
        $this->assertSame('description', $optionConfig->getDescription());
        $this->assertSame('t', $optionConfig->getShortName());
        $this->assertSame('test', $optionConfig->getName());
        $this->assertTrue($optionConfig->isRequired());
        $this->assertTrue($optionConfig->isRepeatable());
        $argumentConfig = $optionConfig->getArgumentConfig();
        $this->assertSame('arg', $argumentConfig->getName());
        $this->assertFalse($argumentConfig->isRequired());
        $this->assertSame(['a', 'b'], $argumentConfig->getValues());
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidConfigs() {
        (new OptionConfigParser)->parse(['config']);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testNameAndShortNameAreAllMissing() {
        (new OptionConfigParser)->parse([[]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidNameType() {
        (new OptionConfigParser)->parse([['name' => true]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidNameFormat() {
        (new OptionConfigParser)->parse([['name' => '']]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidShortNameType() {
        (new OptionConfigParser)->parse([['name' => true]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidShortNameFormat() {
        (new OptionConfigParser)->parse([['name' => '']]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testValuesConflictBetweenNameAndShortName() {
        (new OptionConfigParser)->parse([['name' => 'x', 'short_name' => 'y']]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidDescriptionType() {
        (new OptionConfigParser)->parse([['name' => 'test', 'description' => false]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testDuplicatedOptions() {
        (new OptionConfigParser)->parse([
            ['name' => 'test'],
            ['name' => 'test'],
        ]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidArgumentConfig() {
        (new OptionConfigParser)->parse([['name' => 'test', 'argument' => false]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testArgumentNameIsMissing() {
        (new OptionConfigParser)->parse([['name' => 'test', 'argument' => []]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidArgumentNameType() {
        (new OptionConfigParser)->parse([[
            'name' => 'test', 'argument' => ['name' => true]
        ]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidArgumentNameFormat() {
        (new OptionConfigParser)->parse([[
            'name' => 'test', 'argument' => ['name' => '']
        ]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidArgumentRequiredType() {
         (new OptionConfigParser)->parse([[
            'name' => 'test', 'argument' => ['name' => 'arg', 'required' => '']
        ]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidArgumentValuesType() {
        (new OptionConfigParser)->parse([[
            'name' => 'test', 'argument' => ['name' => 'arg', 'values' => '']
        ]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidValueType() {
        (new OptionConfigParser)->parse([[
            'name' => 'test',
            'argument' => ['name' => 'arg', 'values' => [true]]
        ]]);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidValueFormat() {
        (new OptionConfigParser)->parse([[
            'name' => 'test',
            'argument' => ['name' => 'arg', 'values' => ['']]
        ]]);
    }
}
