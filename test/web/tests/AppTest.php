<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Web\Test\App;
use Hyperframework\Web\Test\FakeRouter;
use Hyperframework\Web\Test\TestCase as Base;

class AppTest extends Base {
    public function testConstruct() {
        $app = $this->getMockBuilder('Hyperframework\Web\App')
            ->disableOriginalConstructor()
            ->getMock();
        Config::remove('hyperframework.app_root_path');
        $app->__construct(dirname(__DIR__));
        $this->assertNotNull(Config::get('hyperframework.app_root_path'));
    }

    public function testRun() {
        $app = $this->getMockBuilder('Hyperframework\Web\App')
            ->setConstructorArgs([dirname(__DIR__)])
            ->setMethods(['createController'])->getMock();
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->getMock();
        $controller->expects($this->once())->method('run');
        $app->expects($this->once())->method('createController')
            ->willReturn($controller);
        Registry::set('hyperframework.web.test.app', $app);
        App::run();
    }

    public function testCreateController() {
        $app = $this->getMock('Hyperframework\Web\App', [], [dirname(__DIR__)]);
        $router = $this->getMock('Hyperframework\Web\Test\FakeRouter');
        $router->expects($this->once())->method('getControllerClass')
            ->willReturn('Hyperframework\Web\Test\IndexController');
        $router->method('getAction')->willReturn('index');
        $app->method('getRouter')->willReturn($router);
        $this->assertInstanceOf(
            'Hyperframework\Web\Test\IndexController',
            $this->callProtectedMethod(
                $app,
                'createController'
            )
        );
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testCreateControllerWhenControllerClassIsEmpty() {
        Config::set(
            'hyperframework.web.router_class',
            'Hyperframework\Web\Test\FakeRouter'
        );
        $app = new App(dirname(__DIR__));
        $this->callProtectedMethod($app, 'createController');
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testCreateControllerWhenControllerClassDoesNotExist() {
        $router = new FakeRouter;
        $router->setControllerClass('Unknown');
        $app = $this->getMock(
            'Hyperframework\Web\App',
            [],
            [dirname(__DIR__)]
        );
        $app->expects($this->once())->method('getRouter')->willReturn($router);
        $this->callProtectedMethod($app, 'createController');
    }

    public function testInitializeErrorHandler() {
        $app = new App(dirname(__DIR__));
        $this->expectOutputString('Hyperframework\Web\Test\ErrorHandler::run');
        $this->callProtectedMethod(
            $app,
            'initializeErrorHandler',
            ['Hyperframework\Web\Test\ErrorHandler']
        );
    }

    public function testCreateApp() {
        $this->assertInstanceOf(
            'Hyperframework\Web\App',
            $this->callProtectedMethod('Hyperframework\Web\App', 'createApp')
        );
    }

    public function testGetDefaultRouter() {
        $app = new App(dirname(__DIR__));
        $this->assertInstanceOf(
            'Hyperframework\Web\Test\Router', $app->getRouter()
        );
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testGetDefaultRouterClassDoesNotExist() {
        Config::set(
            'hyperframework.app_root_namespace', 'Unknown'
        );
        $app = new App(dirname(__DIR__));
        $app->getRouter();
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testGetCustomRouterClassDoesNotExist() {
        Config::set('hyperframework.web.router_class', 'Unknown');
        $app = new App(dirname(__DIR__));
        $app->getRouter();
    }

    public function testGetCustomRouter() {
        Config::set(
            'hyperframework.web.router_class',
            'Hyperframework\Web\Test\FakeRouter'
        );
        $app = new App(dirname(__DIR__));
        $this->assertInstanceOf(
            'Hyperframework\Web\Test\FakeRouter', $app->getRouter()
        );
    }
}
