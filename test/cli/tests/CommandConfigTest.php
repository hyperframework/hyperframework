<?php
namespace Hyperframework\Cli;

use Hyperframework\Common\Config;
use Hyperframework\Cli\Test\TestCase as Base;

class CommandConfigTest extends Base {
    public function testGetGlobalCommandArgumentConfigs() {
        Config::set('hyperframework.cli.multiple_commands', true);
        $config = new CommandConfig;
        $this->assertSame([], $config->getArgumentConfigs());
    }

    public function testGetDefaultArgumentConfigsOfSubcommand() {
        $commandConfig = $this->mockCommandConfig([
            'class' => 'Hyperframework\Cli\Test\Subcommands\ChildCommand'
        ], 'child');
        $argumentConfigs = $commandConfig->getArgumentConfigs('child');
        $config = $argumentConfigs[0];
        $this->assertSame('arg', $config->getName());
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testGetDefaultArgumentWhenCommandClassNotFound() {
        $commandConfig = $this->mockCommandConfig([
            'class' => 'Unknown'
        ]);
        $argumentConfigs = $commandConfig->getArgumentConfigs();
    }

    /**
     * @expectedException Hyperframework\Common\MethodNotFoundException
     */
    public function testGetDefaultArgumentWhenExecuteMethodNotFound() {
        $commandConfig = $this->mockCommandConfig([
            'class' =>
                'Hyperframework\Cli\Test\Subcommands\NoExecuteMethodCommand'
        ]);
        $argumentConfigs = $commandConfig->getArgumentConfigs();
    }

    /**
     * @expectedException LogicException
     */
    public function testGetDefaultArgumentWhenArrayArgumentIsInvalid() {
        $commandConfig = $this->mockCommandConfig([
            'class' =>
            'Hyperframework\Cli\Test\Subcommands\\'
                . 'InvalidArrayArgumentCommand'
        ]);
        $argumentConfigs = $commandConfig->getArgumentConfigs();
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidArgumentConfigs() {
        $commandConfig = $this->mockCommandConfig([
            'name' => 'test',
            'arguments' => false,
        ]);
        $this->assertSame([], $commandConfig->getArgumentConfigs());
    }

    public function testGetArgumentConfigsOfSubcommand() {
        Config::set('hyperframework.cli.multiple_commands', true);
        $config = new CommandConfig;
        $argumentConfigs = $config->getArgumentConfigs('child');
        $config = $argumentConfigs[0];
        $this->assertSame('arg', $config->getName());
    }

    public function testGetClass() {
        $commandConfig = $this->mockCommandConfig([
            'class' => 'Class'
        ]);
        $this->assertSame('Class', $commandConfig->getClass());
    }

    public function testGetDefaultClass() {
        $commandConfig = $this->mockCommandConfig();
        $this->assertSame(
            'Hyperframework\Cli\Test\Command', $commandConfig->getClass()
        );
    }

    public function testGetDefaultSubcommandClass() {
        $commandConfig = $this->mockCommandConfig([], 'test-child');
        $this->assertSame(
            'Hyperframework\Cli\Test\Subcommands\TestChildCommand',
            $commandConfig->getClass('test-child')
        );
    }

    public function testGetSubcommandClass() {
        $commandConfig = $this->mockCommandConfig([
            'class' => 'Class'
        ], 'child');
        $this->assertSame('Class', $commandConfig->getClass('child'));
    }

    public function testGetSubcommandDefaultClass() {
        $commandConfig = $this->mockCommandConfig([], 'child');
        $this->assertSame(
            'Hyperframework\Cli\Test\Subcommands\ChildCommand',
            $commandConfig->getClass('child')
        );
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetInvalidClass() {
        $commandConfig = $this->mockCommandConfig([
            'class' => false
        ]);
        $commandConfig->getClass();
    }

    public function testGetOptionConfig() {
        $commandConfig = $this->mockCommandConfig([
            'options' => [['name' => 'test']]
        ]);
        $config = $commandConfig->getOptionConfig('test');
        $this->assertInstanceOf('Hyperframework\Cli\OptionConfig', $config);
    }

    public function testDefaultHelpAndVersionOptionConfigs() {
        $commandConfig = $this->mockCommandConfig();
        $this->assertNotNull($commandConfig->getOptionConfig('help'));
        $this->assertNull($commandConfig->getOptionConfig('version'));
        $commandConfig = $this->mockCommandConfig(['version' => 1]);
        $this->assertNotNull($commandConfig->getOptionConfig('version'));
    }

    public function testGetOptionConfigOfSubcommand() {
        $commandConfig = $this->mockCommandConfig([
            'options' => [['name' => 'test']]
        ], 'child');
        $config = $commandConfig->getOptionConfig('test', 'child');
        $this->assertNotNull($config);
    }

    public function testGetOptionConfigs() {
        $commandConfig = $this->mockCommandConfig([
            'options' => [['name' => 'test']]
        ]);
        $configs = $commandConfig->getOptionConfigs();
        $this->assertSame(1, count($configs));
        $this->assertSame('test', $configs[0]->getName());
    }

    public function testGetOptionConfigsOfSubcommand() {
        $commandConfig = $this->mockCommandConfig([
            'options' => [['name' => 'test']]
        ], 'child');
        $configs = $commandConfig->getOptionConfigs('child');
        $this->assertSame(1, count($configs));
        $this->assertSame('test', $configs[0]->getName());
    }

    public function testDefaultHelpOptionConfigOfSubcommand() {
        $commandConfig = $this->mockCommandConfig([], 'child');
        $this->assertNotNull($commandConfig->getOptionConfig('help', 'child'));
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetInvalidOptionConfigs() {
        $commandConfig = $this->mockCommandConfig([
            'options' => false
        ]);
        $commandConfig->getOptionConfigs();
    }

    public function testGetMutuallyExclusiveOptionGroupConfigs() {
        $commandConfig = $this->mockCommandConfig([
            'options' => [['name' => 'test'], ['name' => 'test2']],
            'mutually_exclusive_option_groups' => [['test', 'test2']]
        ]);
        $configs = $commandConfig->getMutuallyExclusiveOptionGroupConfigs();
        $config = $configs[0];
        $this->assertSame(2, count($config->getOptionConfigs()));
    }

    public function testGetMutuallyExclusiveOptionGroupConfigsOfSubcommand() {
        $commandConfig = $this->mockCommandConfig([
            'options' => [['name' => 'test'], ['name' => 'test2']],
            'mutually_exclusive_option_groups' => [['test', 'test2']]
        ], 'child');
        $configs = $commandConfig->getMutuallyExclusiveOptionGroupConfigs(
            'child'
        );
        $config = $configs[0];
        $this->assertSame(2, count($config->getOptionConfigs()));
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetInvalidMutuallyExclusiveOptionGroupConfigs() {
        $commandConfig = $this->mockCommandConfig([
            'mutually_exclusive_option_groups' => false
        ]);
        $commandConfig->getMutuallyExclusiveOptionGroupConfigs();
    }

    public function testGetDescription() {
        $commandConfig = $this->mockCommandConfig([
            'description' => 'test'
        ]);
        $this->assertSame('test', $commandConfig->getDescription());
    }

    public function testGetName() {
        $commandConfig = $this->mockCommandConfig([
            'name' => 'test'
        ]);
        $this->assertSame('test', $commandConfig->getName());
    }

    public function testGetVersion() {
        $commandConfig = $this->mockCommandConfig([
            'version' => '1.0.0'
        ]);
        $this->assertSame('1.0.0', $commandConfig->getVersion());
    }

    public function testisMultipleCommandMode() {
        $commandConfig = new CommandConfig;
        Config::set('hyperframework.cli.multiple_commands', true);
        $this->assertTrue($commandConfig->isMultipleCommandMode());
        Config::set('hyperframework.cli.multiple_commands', false);
        $this->assertTrue($commandConfig->isMultipleCommandMode());
    }

    public function testHasSubcommand() {
        $commandConfig = $this->getMockBuilder('Hyperframework\Cli\CommandConfig')
            ->setMethods(['getSubcommandNames'])->getMock();
        $commandConfig->method('getSubcommandNames')->willReturn(['test']);
        $this->assertTrue($commandConfig->hasSubcommand('test'));
    }

    public function testGetSubcommandNames() {
        Config::set('hyperframework.cli.multiple_commands', true);
        $commandConfig = new CommandConfig;
        $this->assertSame(2, count($commandConfig->getSubcommandNames()));
    }

    /**
     * @expectedException LogicException
     */
    public function testGetSubcommandNamesWhenSubcommandIsDisabled() {
        $commandConfig = new CommandConfig;
        $commandConfig->getSubcommandNames();
    }

    /**
     * @expectedException LogicException
     */
    public function testGetAllByInvalidSubcommand() {
        $commandConfig = new CommandConfig;
        $this->callProtectedMethod($commandConfig, 'getAll', ['unknown']);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetAllWhenConfigFileIsMissing() {
        Config::set('hyperframework.cli.command_config_path', 'unknown');
        $commandConfig = new CommandConfig;
        $this->callProtectedMethod($commandConfig, 'getAll');
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetAllWhenConfigFileIsInvalid() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'invalid_config_command.php'
        );
        $commandConfig = new CommandConfig;
        $this->callProtectedMethod($commandConfig, 'getAll');
    }

    public function testCustomizeSubcommandConfigRootPath() {
        Config::set('hyperframework.cli.multiple_commands', true);
        Config::set(
            'hyperframework.cli.subcommand_config_root_path',
            'custom_subcommands_config_root'
        );
        $commandConfig = new CommandConfig;
        $this->assertSame(
            ['root-path-test-child'], $commandConfig->getSubcommandNames()
        );
    }

    private function mockCommandConfig(
        array $config = [], $subcommandName = null
    ) {
        $result = $this->getMockBuilder('Hyperframework\Cli\CommandConfig')
            ->setMethods(['getAll', 'getSubcommandNames'])->getMock();
        $result->method('getAll')->will(
            $this->returnCallback(function($arg = null)
                use ($config, $subcommandName) {
                    if ($arg === $subcommandName) {
                        return $config;
                    } else {
                        $this->fail('Config is missing.');
                    }
                }
            )
        );
        $result->method('getSubcommandNames')->willReturn(['child']);
        return $result;
    }
}
