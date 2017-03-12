<?php
namespace Hyperframework\Common;

abstract class App {
    private $isQuitMethodCalled = false;

    /**
     * @param string $rootPath
     */
    public function __construct($rootPath) {
        Config::set('hyperframework.app_root_path', $rootPath);
        if (Config::getBool('hyperframework.initialize_config', true)) {
            $this->initializeConfig();
        }
        if (Config::getBool(
            'hyperframework.initialize_error_handler', true
        )) {
            $this->initializeErrorHandler();
        }
    }

    /**
     * @return void
     */
    public function quit() {
        if ($this->isQuitMethodCalled) {
            throw new InvalidOperationException(
                "The quit method of class '" . __CLASS__
                    . "' cannot be called more than once."
            );
        }
        $this->isQuitMethodCalled = true;
        $this->finalize();
        ExitHelper::exitScript();
    }

    /**
     * @return void
     */
    protected function initializeConfig() {
        Config::importFile('init.php');
        $path = ConfigFileFullPathBuilder::build('env.php');
        if (file_exists($path)) {
            Config::importFile($path);
        }
    }

    /**
     * @param string $customDefaultClass
     * @return void
     */
    protected function initializeErrorHandler($customDefaultClass = null) {
        $defaultClass = $customDefaultClass === null ?
            ErrorHandler::class : $customDefaultClass;
        $class = Config::getClass(
            'hyperframework.error_handler.class', $defaultClass
        );
        $handler = new $class;
        $handler->run();
    }

    /**
     * @return void
     */
    protected function finalize() {}
}
