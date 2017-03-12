<?php
namespace Hyperframework\Web;

use Hyperframework\Common\FileFullPathBuilder;
use Hyperframework\Web\Test\OpenView;
use Hyperframework\Web\Test\TestCase as Base;

class ViewKernelTest extends Base {
    public function testRender() {
        $this->expectOutputString('view: index/index');
        $tpl = new OpenView;
        $tpl->render('index/index.html.php');
    }

    public function testRenderByFullPath() {
        $this->expectOutputString('view: index/index');
        $viewPath = FileFullPathBuilder::build(
            'views' . DIRECTORY_SEPARATOR . 'index'
                . DIRECTORY_SEPARATOR . 'index.html.php'
        );
        $tpl = new OpenView;
        $tpl->render($viewPath);
    }

    public function testRenderLayout() {
        $this->expectOutputString("begin content end\n");
        $view = new View;
        $view->render('index/view_with_layout.php');
    }

    public function testRenderNestedLayout() {
        $this->expectOutputString("begin begin-sub content end-sub end\n");
        $view = new View;
        $view->render('index/view_with_nested_layout.php');
    }

    public function testRenderNestedViewWithLayout() {
        $this->expectOutputString("begin-out begin content end\n end-out\n");
        $view = new View;
        $view->render('index/nested_view.php');
    }

    /**
     * @expectedException Hyperframework\Web\ViewException
     */
    public function testRenderByEmptyPath() {
        $tpl = new OpenView;
        $tpl->render(null);
    }

    public function testRenderBlock() {
        $tpl = new OpenView;
        $isRendered = false;
        $tpl->setBlock('name', function() use (&$isRendered) {
            $isRendered = true;
        });
        $tpl->renderBlock('name');
        $this->assertTrue($isRendered);
    }

    public function testRenderDefaultBlock() {
        $tpl = new OpenView;
        $isRendered = false;
        $tpl->renderBlock('undefined', function() use (&$isRendered) {
            $isRendered = true;
        });
        $this->assertTrue($isRendered);
    }

    public function testRenderViewWithLayoutInBlock() {
        $this->expectOutputString("begin content end\n");
        $tpl = new View;
        $tpl->setBlock('name', function() use ($tpl) {
            $tpl->render('index/view_with_layout.php');
        });
        $tpl->renderBlock('name');
    }

    public function testIssetViewModelField() {
        $tpl = new OpenView(['name' => 'value']);
        $this->assertTrue(isset($tpl['name']));
        $this->assertFalse(isset($tpl['unknown']));
    }

    public function testGetViewModelField() {
        $tpl = new OpenView(['name' => 'value']);
        $this->assertSame('value', $tpl['name']);
    }

    /**
     * @expectedException Hyperframework\Web\ViewException
     */
    public function testGetViewModelFieldWhichDoesNotExist() {
        $tpl = new OpenView([]);
        $tpl['unknown'];
    }

    public function testUnsetViewModelField() {
        $tpl = new OpenView(['name' => 'value']);
        $this->assertTrue(isset($tpl['name']));
        unset($tpl['name']);
        $this->assertFalse(isset($tpl['name']));
    }

    /**
     * @expectedException Hyperframework\Web\ViewException
     */
    public function testRenderBlockWhichDoesNotExist() {
        $tpl = new OpenView;
        $tpl->renderBlock('undefined');
    }
}
