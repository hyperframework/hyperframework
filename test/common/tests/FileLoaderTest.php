<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class FileLoaderTest extends Base {
    public function testLoadPhp() {
        $this->assertTrue(FileLoader::loadPhp('data/php.php'));
    }

    public function testLoadData() {
        $this->assertSame("content\n", FileLoader::loadData('data/text.txt'));
    }

    public function testGetFullPath() {
        $path = $this->callProtectedMethod(
            'Hyperframework\Common\FileLoader', 'getFullPath', ['']
        );
        $this->assertSame(dirname(__DIR__), $path);
    }
}
