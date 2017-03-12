<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class FilePathCombinerTest extends Base {
    public function testAppend() {
        $path = 'dir';
        $path = FilePathCombiner::combine($path, 'file');
        $this->assertSame('dir' . DIRECTORY_SEPARATOR . 'file', $path);
    }

    public function testAppendRootPath() {
        $path = DIRECTORY_SEPARATOR;
        $path = FilePathCombiner::combine($path, 'file');
        $this->assertSame(DIRECTORY_SEPARATOR . 'file', $path);
    }

    public function testAppendPathWhichEndsWithDirectorySeparator() {
        $path = 'dir' . DIRECTORY_SEPARATOR;
        $path = FilePathCombiner::combine($path, 'file');
        $this->assertSame('dir' . DIRECTORY_SEPARATOR . 'file', $path);
    }

    public function testAppendEmpty() {
        $path = 'file';
        $path = FilePathCombiner::combine($path, null);
        $this->assertSame('file', $path);
    }
}
