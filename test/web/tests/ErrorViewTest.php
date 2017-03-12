<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;
use Hyperframework\Web\Test\TestCase as Base;

class ErrorViewTest extends Base {
    public function testRenderView() {
        $this->expectOutputString("error\n");
        $view = new ErrorView;
        $view->render(404, 'not found', null);
    }

    public function testRenderDefaultView() {
        Config::set(
            'hyperframework.web.error_view.root_path', 'invalid'
        );
        $this->expectOutputString('404 not found');
        $engine = $this->getMock('Hyperframework\Web\ResponseEngine');
        $engine->expects($this->once())->method('setHeader')->with(
            'Content-Type: text/plain; charset=utf-8'
        );
        Response::setEngine($engine);
        $view = new ErrorView;
        $view->render(404, 'not found', null);
    }

    public function testRenderViewByStatusCode() {
        $this->expectOutputString("500\n");
        $view = new ErrorView;
        $view->render(500, '', null);
    }

    public function testCustomizeViewRoot() {
        Config::set(
            'hyperframework.web.view.root_path',
            'views/_custom_view_root'
        );
        $this->expectOutputString("custom view root: error\n");
        $view = new ErrorView;
        $view->render(500, '', null);
    }

    public function testCustomizeErrorViewRoot() {
        Config::set(
            'hyperframework.web.error_view.root_path',
            'views/_custom_error_view_root'
        );
        $this->expectOutputString("custom error view root: error\n");
        $view = new ErrorView;
        $view->render(500, '', null);
    }
}
