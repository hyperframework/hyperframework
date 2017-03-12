<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class FileFullPathBuilderTest extends Base {
    public function setUp() {
        Config::set('hyperframework.app_root_path', '/root');
    }

    public function testBuild() {
        $this->assertSame('/root/path', FileFullPathBuilder::build('path'));
    }

    public function testBuildByFullPath() {
        $this->assertSame('/path', FileFullPathBuilder::build('/path'));
    }

    public function testBuildByEmptyPath() {
        $this->assertSame('/root', FileFullPathBuilder::build(''));
    }

    public function testGetRootPath() {
        $path = $this->callProtectedMethod(
            'Hyperframework\Common\FileFullPathBuilder', 'getRootPath'
        );
        $this->assertSame('/root' , $path);
    }
}
