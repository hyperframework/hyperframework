<?php
namespace Hyperframework\Cli;

use Hyperframework\Cli\Test\App;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Cli\Test\TestCase as Base;

class AppTest extends Base {
    public function createApp($shouldCallConstructor = true) {
        $mock = $this->getMockBuilder('Hyperframework\Cli\App')
            ->setMethods([
                'quit', 'initializeConfig', 'initializeErrorHandler'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        if ($shouldCallConstructor) {
            $mock->__construct(dirname(__DIR__));
        }
        return $mock;
    }

    public function testRun() {
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->disableOriginalConstructor()
            ->setMethods(['executeCommand', 'finalize'])->getMock();
        $app->expects($this->once())->method('executeCommand');
        $app->expects($this->once())->method('finalize');
        Registry::set('hyperframework.cli.test.app', $app);
        App::run('');
    }

    public function testCreateApp() {
        $_SERVER['argv'] = ['run', '-t', 'arg'];
        $this->assertInstanceOf(
            'Hyperframework\Cli\App',
            $this->callProtectedMethod(
                'Hyperframework\Cli\App', 'createApp', [dirname(__DIR__)]
            )
        );
    }

    public function testInitializeOption() {
        $_SERVER['argv'] = ['run', '-t', 'arg'];
        $app = $this->createApp();
        $this->assertEquals(['t' => true], $app->getOptions());
    }

    public function testInitializeArgument() {
        $_SERVER['argv'] = ['run', 'arg'];
        $app = $this->createApp();
        $this->assertEquals($app->getArguments(), ['arg']);
    }

    public function testExecuteCommand() {
        $this->expectOutputString('Hyperframework\Cli\Test\Command::execute');
        $_SERVER['argv'] = ['run', 'arg'];
        $app = $this->createApp();
        $this->callProtectedMethod($app, 'executeCommand', [dirname(__dir__)]);
    }

    public function testGetOption() {
        $_SERVER['argv'] = ['run', '-t', 'arg'];
        $app = $this->createApp();
        $this->assertTrue($app->getOption('t'));
    }

    public function testHasOption() {
        $_SERVER['argv'] = ['run', '-t', 'arg'];
        $app = $this->createApp();
        $this->assertTrue($app->hasOption('t'));
        $this->assertFalse($app->hasOption('x'));
    }

    public function testCustomHelp() {
        $this->expectOutputString('Hyperframework\Cli\Test\Help::render');
        Config::set(
            'hyperframework.cli.help_class', 'Hyperframework\Cli\Test\Help'
        );
        $_SERVER['argv'] = ['run', '-h'];
        $app = $this->createApp();
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testCustomHelpClassNotFound() {
        Config::set(
            'hyperframework.cli.help_class', 'Unknown'
        );
        $_SERVER['argv'] = ['run', '-h'];
        $app = $this->createApp();
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testCommandClassNotFound() {
        Config::set(
            'hyperframework.cli.command_config_path',
            'invalid_class_command.php'
        );
        $_SERVER['argv'] = ['run'];
        $app = $this->createApp();
        $this->callProtectedMethod($app, 'executeCommand', [dirname(__dir__)]);
    }

    public function testRenderHelp() {
        $this->expectOutputString(
            "Usage: test [-t] [-h|--help] [--version] <arg>" . PHP_EOL
        );
        $_SERVER['argv'] = ['run', '-h'];
        $app = $this->createApp(false);
        $app->expects($this->once())->method('quit');
        $app->__construct(dirname(__DIR__));
    }

    public function testRenderVersion() {
        $this->expectOutputString("1.0.0" . PHP_EOL);
        $_SERVER['argv'] = ['run', '--version'];
        $app = $this->createApp(false);
        $app->expects($this->once())->method('quit');
        $app->__construct(dirname(__DIR__));
    }

    public function testCustomCommandConfig() {
        Config::set(
            'hyperframework.cli.command_config_class',
            'Hyperframework\Cli\Test\CommandConfig'
        );
        $_SERVER['argv'] = ['run', 'arg'];
        $app = $this->createApp();
        $this->assertInstanceOf(
            'Hyperframework\Cli\Test\CommandConfig', $app->getCommandConfig()
        );
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testCustomCommandConfigClassNotFound() {
        Config::set(
            'hyperframework.cli.command_config_class', 'Unknown'
        );
        $_SERVER['argv'] = ['run', 'arg'];
        $app = $this->createApp();
    }

    public function testVersionUndefined() {
        $this->expectOutputString("undefined" . PHP_EOL);
        $_SERVER['argv'] = ['run', '--version'];
        $commandConfig = $this->getMockBuilder('Hyperframework\Cli\CommandConfig')
            ->setMethods(['getVersion', 'getOptionConfigs'])->getMock();
        $commandConfig->method('getVersion')->willReturn(null);
        $commandConfig->method('getOptionConfigs')->willReturn([
            'version' =>
                new OptionConfig('version', null, false, false, null, null)
        ]);
        $app = $this->getMockBuilder('Hyperframework\Cli\App')
            ->setMethods([
                'quit',
                'initializeConfig',
                'initializeErrorHandler',
                'getCommandConfig'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getCommandConfig')->willReturn($commandConfig);
        $app->__construct(dirname(__DIR__));
    }

    public function testCommandParsingError() {
        $this->expectOutputString(
            "Unknown option 'unknown'."
                . PHP_EOL . "See 'test --help'." . PHP_EOL
        );
        $_SERVER['argv'] = ['run', '--unknown'];
        $app = $this->createApp(false);
        $app->__construct(dirname(__DIR__));
    }
}
