<?php
namespace Hyperframework\Cli;

use Hyperframework\Common\InvalidOperationException;

abstract class Command {
    private $app;

    /**
     * @param App $app
     */
    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * @return App
     */
    public function getApp() {
        return $this->app;
    }

    /**
     * @return string[]
     */
    public function getArguments() {
        $app = $this->getApp();
        return $app->getArguments();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption($name) {
        $app = $this->getApp();
        return $app->hasOption($name);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getOption($name) {
        $app = $this->getApp();
        return $this->getApp()->getOption($name);
    }

    /**
     * @return string[]
     */
    public function getOptions() {
        $app = $this->getApp();
        return $this->getApp()->getOptions();
    }
}
