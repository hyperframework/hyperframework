<?php
namespace Hyperframework\Cli;

use Hyperframework\Common\Config;
use Hyperframework\Common\ClassNotFoundException;

class MultipleCommandApp extends App {
    private $commandConfig;
    private $subcommandName;
    private $globalOptions = [];

    /**
     * @param string $rootPath
     */
    public function __construct($rootPath) {
        Config::set('hyperframework.cli.multiple_commands', true);
        parent::__construct($rootPath);
    }

    /**
     * @return string[]
     */
    public function getGlobalOptions() {
        return $this->globalOptions;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getGlobalOption($name) {
        if (isset($this->globalOptions[$name])) {
            return $this->globalOptions[$name];
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasGlobalOption($name) {
        return isset($this->globalOptions[$name]);
    }

    /**
     * @return bool
     */
    public function hasSubcommand() {
        return $this->subcommandName !== null;
    }

    /**
     * @return string
     */
    public function getSubcommandName() {
        return $this->subcommandName;
    }

    /**
     * @return void
     */
    protected function initializeCommandOptionsAndArguments() {
        $elements = $this->parseCommand();
        if (isset($elements['global_options'])) {
            $this->setGlobalOptions($elements['global_options']);
        }
        if (isset($elements['subcommand_name'])) {
            $this->setSubcommandName($elements['subcommand_name']);
            if (isset($elements['options'])) {
                $this->setOptions($elements['options']);
            }
            if (isset($elements['arguments'])) {
                $this->setArguments($elements['arguments']);
            }
        }
        if ($this->hasGlobalOption('help') || $this->hasOption('help')) {
            $this->renderHelp();
            $this->quit();
        }
        if ($this->hasGlobalOption('version')) {
            $this->renderVersion();
            $this->quit();
        }
    }

    /**
     * @param string[] $options
     * @return void
     */
    protected function setGlobalOptions($globalOptions) {
        $this->globalOptions = $globalOptions;
    }

    /**
     * @param string $options
     * @return void
     */
    protected function setSubcommandName($subcommandName) {
        $this->subcommandName = $subcommandName;
    }

    /**
     * @return void
     */
    protected function executeCommand() {
        if ($this->hasSubcommand()) {
            $this->executeSubcommand();
        } else {
            $this->executeGlobalCommand();
        }
    }

    /**
     * @return void
     */
    protected function executeSubcommand() {
        $commandConfig = $this->getCommandConfig();
        $name = $this->getSubcommandName();
        $class = $commandConfig->getClass($name);
        if (class_exists($class) === false) {
            throw new ClassNotFoundException(
                "Subcommand class '$class' does not exist."
            );
        }
        $subcommand = new $class($this);
        $arguments = $this->getArguments();
        call_user_func_array([$subcommand, 'execute'], $arguments);
    }

    /**
     * @return void
     */
    protected function executeGlobalCommand() {
        $this->renderHelp();
    }
}
