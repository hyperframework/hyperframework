<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;
use Hyperframework\Web\Test\TestCase as Base;

class ViewPathBuilderTest extends Base {
    public function testBuild() {
        $this->assertSame(
            'index.json.php', ViewPathBuilder::build('index', 'json')
        );
    }

    public function testBuildWithoutOutputFormat() {
        Config::set(
            'hyperframework.web.view.filename.include_output_format', false
        );
        $this->assertSame('index.php', ViewPathBuilder::build('index'));
    }

    public function testBuildWithCustomDefaultOutputFormat() {
        Config::set('hyperframework.web.view.default_output_format', 'json');
        $this->assertSame('index.json.php', ViewPathBuilder::build('index'));
    }

    public function testBuildWithEmptyDefaultOutputFormat() {
        Config::set('hyperframework.web.view.default_output_format', '');
        $this->assertSame('index.php', ViewPathBuilder::build('index'));
    }

    public function testBuildWithCustomFormat() {
        Config::set('hyperframework.web.view.format', 'tpl');
        $this->assertSame('index.html.tpl', ViewPathBuilder::build('index'));
    }

    public function testBuildWithEmptyFormat() {
        Config::set('hyperframework.web.view.format', '');
        $this->assertSame('index.html', ViewPathBuilder::build('index'));
    }
}
