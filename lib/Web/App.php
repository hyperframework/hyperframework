<?php
namespace Hyperframework\Web;

use UnexpectedValueException;
use Hyperframework\Common\Config;
use Hyperframework\Common\NamespaceCombiner;
use Hyperframework\Common\ClassNotFoundException;
use Hyperframework\Common\App as Base;

class App extends Base {
    private $router;

    /**
     * @return void
     */
    public static function run() {
        $app = static::createApp();
        $controller = $app->createController();
        $controller->run();
    }

    /**
     * @return Router
     */
    public function getRouter() {
        if ($this->router === null) {
            $configName = 'hyperframework.web.router_class';
            $class = Config::getClass($configName);
            if ($class === null) {
                $class = 'Router';
                $namespace = Config::getAppRootNamespace();
                if ($namespace !== '' && $namespace !== '\\') {
                    $class = NamespaceCombiner::combine($namespace, $class);
                }
                if (class_exists($class) === false) {
                    throw new ClassNotFoundException(
                        "Router class '$class' does not exist,"
                            . " can be changed using config '$configName'."
                    );
                }
            }
            $this->router = new $class;
        }
        return $this->router;
    }

    /**
     * @return static
     */
    protected static function createApp() {
        return new static(dirname(getcwd()));
    }

    /**
     * @return Controller
     */
    protected function createController() {
        $router = $this->getRouter();
        $router->execute();
        $class = (string)$router->getControllerClass();
        if ($class === '') {
            throw new UnexpectedValueException(
                'The controller class cannot be empty.'
            );
        }
        if (class_exists($class) === false) {
            throw new ClassNotFoundException(
                "Controller class '$class' does not exist."
            );
        }
        return new $class($this);
    }

    /**
     * @param string $customDefaultClass
     * @return void
     */
    protected function initializeErrorHandler($customDefaultClass = null) {
        $defaultClass = $customDefaultClass === null ?
            ErrorHandler::class : $customDefaultClass;
        parent::initializeErrorHandler($defaultClass);
    }
}
