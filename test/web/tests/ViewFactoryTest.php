<?php
namespace Hyperframework\Web;

use stdClass;
use Hyperframework\Common\Config;
use Hyperframework\Web\Test\TestCase as Base;

class ViewFactoryTest extends Base {
    public function testCreateView() {
        $object = new stdClass;
        $viewModel = ['object' => $object];
        $view = ViewFactory::createView($viewModel);
        $this->assertInstanceOf('Hyperframework\Web\View', $view);
        $this->assertSame($object, $view['object']);
    }

    public function testCreateViewByConfig() {
        Config::set('hyperframework.web.view.class', 'stdClass');
        $this->assertInstanceOf('stdClass', ViewFactory::createView());
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testCreateViewByInvalidConfig() {
        Config::set('hyperframework.web.view.class', 'Unknown');
        ViewFactory::createView();
    }
}
