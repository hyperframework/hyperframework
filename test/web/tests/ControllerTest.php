<?php
namespace Hyperframework\Web;

use Hyperframework\Web\Test\Exception;
use Hyperframework\Common\Config;
use Hyperframework\Web\Test\IndexController;
use Hyperframework\Web\Test\ParentConstructorNotCalledController;
use Hyperframework\Common\NotSupportedException;
use Hyperframework\Web\Test\TestCase as Base;

class ControllerTest extends Base {
    private $isExitCalled;

    protected function setUp() {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Config::set(
            'hyperframework.web.router_class',
            'Hyperframework\Web\Test\FakeRouter'
        );
    }

    public function testCheckCsrf() {
        Config::set('hyperframework.web.csrf_protection.enable', true);
        $engine = $this->getMock('Hyperframework\Web\CsrfProtectionEngine');
        $engine->expects($this->once())->method('run');
        CsrfProtection::setEngine($engine);
        $this->testRun();
    }

    public function testCheckCsrfWhenProtectionIsDisabled() {
        $engine = $this->getMock('Hyperframework\Web\CsrfProtectionEngine');
        $engine->expects($this->never())->method('run');
        CsrfProtection::setEngine($engine);
        $this->testRun();
    }

    public function testRun() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setActionMethod('onIndexAction');
        $router->setController('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])
            ->setMethods(['handleAction', 'finalize'])->getMock();
        $recorder = [];
        $controller->addBeforeFilter(function() use (&$recorder) {
            $recorder[] = 'before';
        });
        $controller->addAfterFilter(function() use (&$recorder) {
            $recorder[] = 'after';
        });
        $controller->expects($this->once())->method('handleAction')
            ->will($this->returnCallback(function() use (&$recorder) {
                $recorder[] = 'handle_action';
            }));
        $controller->expects($this->once())->method('finalize')
            ->will($this->returnCallback(function() use (&$recorder) {
                $recorder[] = 'finalize';
            }));
        $controller->run();
        $this->assertSame(
            ['before', 'handle_action', 'after', 'finalize'], $recorder
        );
    }

    /**
     * @expectedException Hyperframework\Common\InvalidOperationException
     */
    public function testRunTwice() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $controller->run();
        $controller->run();
    }

    /**
     * @requires PHP 5.5
     */
    public function testRunWhenExceptionIsThrown() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setActionMethod('onIndexAction');
        $router->setController('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $controller->expects($this->never())->method('handleAction');
        $isCaught = false;
        $controller->addAroundFilter(function() use (&$isCaught) {
            try {
                yield;
            } catch (Exception $e) {
                $isCaught = true;
                throw $e;
            }
        });
        $controller->addBeforeFilter(function() {
            throw new Exception;
        });
        try {
            $controller->run();
            $this->fail('Exception has been eaten.');
        } catch (Exception $e) {}
        $this->assertTrue($isCaught);
    }

    /**
     * @requires PHP 5.5
     */
    public function testEatExceptionInAroundFilter() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setActionMethod('onIndexAction');
        $router->setController('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])
            ->setMethods(['handleAction', 'finalize'])->getMock();
        $isAfterFilterACalled = false;
        $controller->addAfterFilter(function() use (&$isAfterFilterACalled) {
            $isAfterFilterACalled = true;
        });
        $controller->addAroundFilter(function() {
            try {
                yield;
            } catch (Exception $e) {
            }
        });
        $isAfterFilterBCalled = false;
        $controller->addAfterFilter(function() use (&$isAfterFilterBCalled) {
            $isAfterFilterBCalled = true;
        });
        $controller->addAfterFilter(function() {
            throw new Exception;
        });
        $controller->run();
        $this->assertTrue($isAfterFilterACalled);
        $this->assertFalse($isAfterFilterBCalled);
    }

    public function testGetView() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setModule('admin');
        $router->setAction('index');
        $router->setController('index');
        $controller = new IndexController($app);
        $this->assertSame('admin/index/index.html.php', $controller->getView());
    }

    public function testGetViewWhenModuleDoesNotExist() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setController('index');
        $controller = new IndexController($app);
        $this->assertSame('index/index.html.php', $controller->getView());
    }

    public function testGetViewWhenControllerIsEmpty() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = new IndexController($app);
        $this->assertSame('index.html.php', $controller->getView());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testGetViewWhenActionIsEmpty() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setController('index');
        $controller = new IndexController($app);
        $controller->getView();
    }

    public function testRenderView() {
        $this->expectOutputString('view: index/index');
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setController('index');
        $controller = new IndexController($app);
        $controller->renderView();
    }

    public function testRenderViewObject() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['getView'])->getMock();
        $view = $this->getMock('Hyperframework\Web\Test\View');
        $view->expects($this->once())->method('render')->with([]);
        $controller->setActionResult([]);
        $controller->expects($this->once())
            ->method('getView')->willReturn($view);
        $controller->renderView();
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidViewType() {
        $app = new App(dirname(__DIR__));
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['getView'])->getMock();
        $controller->expects($this->once())
            ->method('getView')->willReturn(false);
        $controller->renderView();
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidViewPath() {
        $app = new App(dirname(__DIR__));
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['getView'])->getMock();
        $controller->expects($this->once())
            ->method('getView')->willReturn('');
        $controller->renderView();
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidViewModel() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setController('index');
        $controller = new IndexController($app);
        $controller->setActionResult(false);
        $controller->renderView();
    }

    public function testQuit() {
        $isExitCalled = false;
        Config::set('hyperframework.exit_function', function() {
            $this->onExit();
        });
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction', 'finalize'])->getMock();
        $isQuitFilterChainCalled = false;
        $isFinalizeCalled = false;
        $controller->addAroundFilter(
            function() use (&$isQuitFilterChainCalled) {
                yield;
                $isQuitFilterChainCalled = true;
            }
        );
        $controller->addBeforeFilter(function() use ($controller) {
            $controller->quit();
        });
        $controller->method('finalize')->will($this->returnCallback(
            function() use (&$isFinalizeCalled, &$isExitCalled) {
                if ($isExitCalled === false) {
                    $isFinalizeCalled = true;
                }
            }
        ));
        $controller->run();
        $this->assertTrue($isQuitFilterChainCalled);
        $this->assertTrue($isFinalizeCalled);
        $this->assertTrue($this->isExitCalled);
    }

    /**
     * @expectedException Hyperframework\Common\InvalidOperationException
     */
    public function testQuitTwice() {
        Config::set('hyperframework.exit_function', function() {
            $this->onExit();
        });
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = new IndexController($app);
        $controller->quit();
        $controller->quit();
    }

    public function testRedirect() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])
            ->setMethods(['handleAction', 'quit'])->getMock();
        $controller->expects($this->once())->method('quit');
        $engine = $this->getMock('Hyperframework\Web\ResponseEngine');
        $engine->expects($this->once())->method('setHeader')->with(
            'Location: /', true, 302
        );
        Response::setEngine($engine);
        $controller->redirect('/');
    }

    public function testAddBeforeFilter() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setActionMethod('onIndexAction');
        $router->setController('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $isCalled = false;
        $controller->addBeforeFilter(function() use (&$isCalled) {
            $isCalled = true;
        });
        $controller->run();
        $this->assertTrue($isCalled);
    }

    public function testAddAfterFilter() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setActionMethod('onIndexAction');
        $router->setController('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $isCalled = false;
        $controller->addAfterFilter(function() use (&$isCalled) {
            $isCalled = true;
        });
        $controller->run();
        $this->assertTrue($isCalled);
    }

    public function testAddAroundFilter() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setActionMethod('onIndexAction');
        $router->setController('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $recorder = [];
        $controller->addAroundFilter(function() use (&$recorder) {
            $recorder[] = 'before';
            yield;
            $recorder[] = 'after';
        });
        $controller->run();
        $this->assertSame(['before', 'after'], $recorder);
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testFilterClassNotFound() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $controller->addBeforeFilter('Unknown');
        $controller->run();
    }

    public function testPrependFilter() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setActionMethod('onIndexAction');
        $router->setController('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $recorder = [];
        $controller->addBeforeFilter(function() use (&$recorder) {
            $recorder[] = 'a';
        });
        $controller->addBeforeFilter(function() use (&$recorder) {
            $recorder[] = 'b';
        }, ['prepend' => true]);
        $controller->run();
        $this->assertSame(['b', 'a'], $recorder);
    }

    public function testIgnoreActionForFilter() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setActionMethod('onIndexAction');
        $router->setController('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $isCalled = false;
        $controller->addBeforeFilter(function() use (&$isCalled) {
            $isCalled = true;
        }, ['ignored_actions' => ['index']]);
        $controller->run();
        $this->assertFalse($isCalled);
    }

    /**
     * @expectedException Hyperframework\Web\ActionFilterException
     */
    public function testInvalidIgnoreActionsOptionWhenAddFilter() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = new IndexController($app);
        $controller->addBeforeFilter(
            function() {}, ['ignored_actions' => false]
        );
    }

    public function testSpecifyActionForFilter() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $router->setActionMethod('onIndexAction');
        $router->setController('index');
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $isCalled = false;
        $controller->addBeforeFilter(function() use (&$isCalled) {
            $isCalled = true;
        }, ['actions' => ['unknown']]);
        $controller->run();
        $controller = $this->getMockBuilder(
            'Hyperframework\Web\Test\IndexController'
        )->setConstructorArgs([$app])->setMethods(['handleAction'])->getMock();
        $this->assertFalse($isCalled);
        $controller->addBeforeFilter(function() use (&$isCalled) {
            $isCalled = true;
        }, ['actions' => ['index']]);
        $controller->run();
        $this->assertTrue($isCalled);
    }

    /**
     * @expectedException Hyperframework\Web\ActionFilterException
     */
    public function testInvalidActionsOptionWhenAddFilter() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = new IndexController($app);
        $controller->addBeforeFilter(
            function() {}, ['actions' => false]
        );
    }

    /**
     * @expectedException Hyperframework\Web\ActionFilterException
     */
    public function testAddFilterWhichIsEmtpyString() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = new IndexController($app);
        $controller->addBeforeFilter('');
    }

    /**
     * @expectedException Hyperframework\Web\ActionFilterException
     */
    public function testAddFilterWhichTypeIsInvalid() {
        $app = new App(dirname(__DIR__));
        $router = $app->getRouter();
        $router->setAction('index');
        $controller = new IndexController($app);
        $controller->addBeforeFilter($controller);
    }

    public function onExit() {
        $this->isExitCalled = true;
    }
}
