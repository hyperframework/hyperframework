<?php
namespace Hyperframework\Cli;

use Hyperframework\Common\Config;
use Hyperframework\Cli\Test\TestCase as Base;

class HelpTest extends Base {
    public function testRender() {
        $this->expectOutputString(
            'Usage: test [-t] [-h|--help] [--version] <arg>' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderGlobalCommandHelp() {
        Config::set(
            'hyperframework.cli.subcommand_config_root_path',
            'subcommands_for_help_test'
        );
        Config::set('hyperframework.cli.multiple_commands', true);
        $this->expectOutputString(
            'Usage: test [-t] [-h|--help] [--version] <subcommand>' . PHP_EOL
            . PHP_EOL . 'Subcommands:' . PHP_EOL
            . ' child  child description' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\MultipleCommandApp')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $app->method('getSubcommandName')->willReturn(null);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderSubcommandHelp() {
        Config::set(
            'hyperframework.cli.subcommand_config_root_path',
            'subcommands_for_help_test'
        );
        Config::set('hyperframework.cli.multiple_commands', true);
        $this->expectOutputString(
            'Usage: test child [-c] [-h|--help] <arg>' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\MultipleCommandApp')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $app->method('getSubcommandName')->willReturn('child');
        $help = new Help($app);
        $help->render();
    }

    public function testRenderOptionList() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_with_description_command.php'
        );
        $this->expectOutputString(
            'Usage: test [options]' . PHP_EOL
                . PHP_EOL . 'Options:'
                . PHP_EOL . ' --test  description' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderArgument() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'repeatable_optional_argument_command.php'
        );
        $this->expectOutputString(
            'Usage: test <arg> [<arg2>...]' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderMutuallyExclusiveOptions() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'mutually_exclusive_options_command.php'
        );
        $this->expectOutputString(
            'Usage: test (-a|-b)' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderOptionArgumentValues() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_has_argument_values_command.php'
        );
        $this->expectOutputString(
            'Usage: test [--test=(a|b)]' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderOptionalLongOptionArgument() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_argument_is_optional_command.php'
        );
        $this->expectOutputString(
            'Usage: test [-t|--test[=<arg>]] <arg>' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderOptionalShortOptionArgument() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'short_option_argument_is_optional_command.php'
        );
        $this->expectOutputString(
            'Usage: test [-t[<arg>]] <arg>' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderRequiredOption() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'option_is_required_command.php'
        );
        $this->expectOutputString(
            'Usage: test (-t|--test) [--version]' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderAppNameInNewLine() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'short_app_name_command.php'
        );
        $this->expectOutputString(
            'Usage: ab [--name-a] [--name-b] [--name-c] [--name-d] [--name-e]'
                . ' [--name-f] [--name-g]' . PHP_EOL
                . '          <arg>' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderLongUsageElements() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'long_name_options_command.php'
        );
        $this->expectOutputString(
            'Usage: ab [--very-very-very-very-very-very-very-'
                . 'very-very-very-very-long-name-a]' . PHP_EOL
                . '          [--very-very-very-very-very-very-very-'
                . 'very-very-very-very-long-name-b]' . PHP_EOL
                . '          <arg>' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }

    public function testRenderDescriptionWhichStartsWithPhpEol() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'description_starts_with_php_eol_option_command.php'
        );
        $this->expectOutputString(
            'Usage: ab [options] <arg>' . PHP_EOL . PHP_EOL
                . 'Options:' . PHP_EOL
                . ' --test' . PHP_EOL
                . 'description' . PHP_EOL
        );
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn(new CommandConfig);
        $help = new Help($app);
        $help->render();
    }
}
