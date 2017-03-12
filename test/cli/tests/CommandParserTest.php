<?php
namespace Hyperframework\Cli;

use Hyperframework\Common\Config;
use Hyperframework\Cli\Test\TestCase as Base;

class CommandParserTest extends Base {
    public function testParseCommand() {
        $this->assertSame(
            [
                'options' => [],
                'arguments' => ['arg']
            ],
            (new CommandParser)->parse(new CommandConfig, ['run', 'arg'])
        );
    }

    public function testParseSubcommand() {
        Config::set('hyperframework.cli.multiple_commands', true);
        $this->assertSame(
            [
                'global_options' => [],
                'subcommand_name' => 'child',
                'options' => [],
                'arguments' => ['arg']
            ],
            (new CommandParser)->parse(new CommandConfig, ['run', 'child',  'arg'])
        );
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testParseWhenSubcommandDoesNotExist() {
        Config::set('hyperframework.cli.multiple_commands', true);
        (new CommandParser)->parse(new CommandConfig, ['run', 'unknown-subcommand']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testParseWhenGlobalOptionNameIsInvalid() {
        Config::set('hyperframework.cli.multiple_commands', true);
        (new CommandParser)->parse(new CommandConfig, ['run', '--', 'child', 'arg']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testInvalidOptionName() {
        (new CommandParser)->parse(new CommandConfig, ['run', '--test']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testInvalidOptionShortName() {
        (new CommandParser)->parse(new CommandConfig, ['run', '-x']);
    }

    public function testParseShortOptionWithArgument() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_argument_is_required_command.php'
        );
        $result = (new CommandParser)->parse(new CommandConfig, ['run', '-t', 'x']);
        $this->assertSame('x', $result['options']['test']);
    }

    public function testParseShortOptionWithStickedFormArgument() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_argument_is_required_command.php'
        );
        $result = (new CommandParser)->parse(new CommandConfig, ['run', '-tx']);
        $this->assertSame('x', $result['options']['test']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
     public function testParseLongOptionWhenOptionArgumentIsInvalid() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_without_argument_command.php'
        );
        (new CommandParser)->parse(new CommandConfig, ['run', '--test=xx']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testParseShortOptionWhenOptionArgumentIsMissing() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_argument_is_required_command.php'
        );
        (new CommandParser)->parse(new CommandConfig, ['run', '-t']);
    }

    public function testParseLongOptionWithArgument() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_argument_is_required_command.php'
        );
        $result = (new CommandParser)->parse(
            new CommandConfig, ['run', '--test', 'x']
        );
        $this->assertSame('x', $result['options']['test']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testParseLongOptionWhenOptionArgumentIsMissing() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_argument_is_required_command.php'
        );
        (new CommandParser)->parse(new CommandConfig, ['run', '--test']);
    }

    public function testParseRepeatableShortOption() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_is_repeatable_command.php'
        );
        $this->assertSame(
            [
                'options' => ['test' => [true, true]],
                'arguments' => []
            ],
            (new CommandParser)->parse(new CommandConfig, ['run', '-tt'])
        );
    }

    public function testParseRepeatableLongOption() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_is_repeatable_command.php'
        );
        $this->assertSame(
            [
                'options' => ['test' => [true, true]],
                'arguments' => []
            ],
            (new CommandParser)->parse(new CommandConfig, ['run', '--test', '--test'])
        );
    }

    public function testParseOptionWhichHasNameAndShortName() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_has_name_and_short_name_command.php'
        );
        $this->assertSame(
            [
                'options' => ['test' => [true, true]],
                'arguments' => []
            ],
            (new CommandParser)->parse(new CommandConfig, ['run', '-tt'])
        );
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testOptionIsMissing() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_is_required_command.php'
        );
        (new CommandParser)->parse(new CommandConfig, ['run']);
    }

    public function testMagicOption() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_is_required_command.php'
        );
        $result = (new CommandParser)->parse(new CommandConfig, ['run', '--version']);
        $this->assertTrue($result['options']['version']);
    }

    public function testOptionWithEnumeratedValues() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_has_argument_values_command.php'
        );
        $result = (new CommandParser)->parse(
            new CommandConfig, ['run', '--test', 'a']
        );
        $this->assertSame('a', $result['options']['test']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testOptionWithInvalidValue() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_has_argument_values_command.php'
        );
        $result = (new CommandParser)->parse(
            new CommandConfig, ['run', '--test', 'x']
        );
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testMutuallyExclusiveOptionIsMissing() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'mutually_exclusive_options_command.php'
        );
        $result = (new CommandParser)->parse(new CommandConfig, ['run']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testMutuallyExclusiveOption() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'mutually_exclusive_options_command.php'
        );
        $result = (new CommandParser)->parse(new CommandConfig, ['run', '-a', '-b']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testArgumentMissing() {
        (new CommandParser)->parse(new CommandConfig, ['run']);
    }

    /**
     * @expectedException Hyperframework\Cli\CommandParsingException
     */
    public function testInvalidArgument() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'no_argument_command.php'
        );
        $result = (new CommandParser)->parse(new CommandConfig, ['run', 'a']);
    }

    public function testRepeatableArgument() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'repeatable_argument_command.php'
        );
        $result = (new CommandParser)->parse(new CommandConfig, ['run', 'a', 'b']);
        $this->assertSame(['a', 'b'], $result['arguments']);
    }
}
