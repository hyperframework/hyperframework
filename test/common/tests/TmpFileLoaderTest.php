<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class TmpFileLoaderTest extends Base {
    public function testGetRootPath() {
        $path = $this->callProtectedMethod(
            'Hyperframework\Common\TmpFileLoader', 'getFullPath', ['']
        );
        $this->assertSame(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tmp', $path
        );
    }
}

