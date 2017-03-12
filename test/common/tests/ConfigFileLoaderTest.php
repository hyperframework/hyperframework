<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class ConfigFileLoaderTest extends Base {
    public function testGetRootPath() {
        $path = $this->callProtectedMethod(
            'Hyperframework\Common\ConfigFileLoader', 'getFullPath', ['']
        );
        $this->assertSame(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config', $path
        );
    }
}

