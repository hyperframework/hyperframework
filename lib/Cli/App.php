<?php
namespace Hyperframework\Cli;

use Hyperframework\Common\Config;
use Hyperframework\Common\ClassNotFoundException;
use Hyperframework\Common\App as Base;

class App extends Base {
    private $commandConfig;
    private $options = [];
    private $arguments = [];

    /**
     * @param string $rootPath
     * @return void
     */
    public static function run($rootPath) {
        $app = static::createApp($rootPath);
        try {
            $elements = $app->parseCommand();
            $app->setElements($elements);
            $app->executeCommand();
        } catch (CommandParsingException $e) {
            $app->renderCommandParsingError($e);
        }
    }

    /**
     * @return string[]
     */
    public function getArguments() {
        return $this->arguments;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption($name) {
        return isset($this->options[$name]);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getOption($name) {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }
    }

    /**
     * @return string[]
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @return CommandConfig
     */
    public function getCommandConfig() {
        if ($this->commandConfig === null) {
            $class = Config::getClass(
                'hyperframework.cli.command_config_class', CommandConfig::class
            );
            $this->commandConfig = new $class;
        }
        return $this->commandConfig;
    }

    /**
     * @param string $rootPath
     * @return static
     */
    protected static function createApp($rootPath) {
        return new static($rootPath);
    }

    /**
     * @param array $elements
     * @return void
     */
    protected function setElements($elements) {
        if (isset($elements['options'])) {
            $this->setOptions($elements['options']);
        }
        if (isset($elements['arguments'])) {
            $this->setArguments($elements['arguments']);
        }
    }

    /**
     * @param string[] $options
     * @return void
     */
    protected function setOptions($options) {
        $this->options = $options;
    }

    /**
     * @param string[] $arguments
     * @return void
     */
    protected function setArguments($arguments) {
        $this->arguments = $arguments;
    }

    /**
     * @return void
     */
    protected function executeCommand() {
        if ($this->hasOption('help')) {
            $this->renderHelp();
            return;
        }
        if ($this->hasOption('version')) {
            $this->renderVersion();
            return;
        }
        $commandConfig = $this->getCommandConfig();
        $class = $commandConfig->getClass();
        if (class_exists($class) === false) {
            throw new ClassNotFoundException(
                "Command class '$class' does not exist."
            );
        }
        $command = new $class($this);
        $arguments = $this->getArguments();
        call_user_func_array([$command, 'execute'], $arguments);
    }

    /**
     * @return void
     */
    protected function renderHelp() {
        $class = Config::getClass(
            'hyperframework.cli.help_class', Help::class
        );
        $help = new $class($this);
        $help->render();
    }

    /**
     * @return void
     */
    protected function renderVersion() {
        $commandConfig = $this->getCommandConfig();
        $version = (string)$commandConfig->getVersion();
        if ($version === '') {
            echo 'undefined', PHP_EOL;
            return;
        }
        echo $version, PHP_EOL;
    }

    /**
     * @return array
     */
    protected function parseCommand() {
        $class = Config::getClass(
            'hyperframework.cli.command_parser_class', CommandParser::class
        );
        $commandParser = new $class($this->getCommandConfig());
        return $commandParser->parse($_SERVER['argv']);
    }

    /**
     * @param CommandParsingException $commandParsingException
     * @return void
     */
    protected function renderCommandParsingError($commandParsingException) {
        echo $commandParsingException->getMessage(), PHP_EOL;
        $config = $this->getCommandConfig();
        $name = $config->getName();
        $subcommandName = $commandParsingException->getSubcommandName();
        if ($subcommandName !== null) {
            $name .= ' ' . $subcommandName;
        }
        if ($config->getOptionConfig('help', $subcommandName) !== null) {
            echo 'See \'', $name, ' --help\'.', PHP_EOL;
        }
    }
}
