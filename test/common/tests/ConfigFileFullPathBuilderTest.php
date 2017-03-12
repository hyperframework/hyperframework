<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class ConfigFileFullPathBuilderTest extends Base {
    public function testGetRootPath() {
        $path = $this->callProtectedMethod(
            'Hyperframework\Common\ConfigFileFullPathBuilder', 'getRootPath'
        );
        $this->assertSame(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config', $path
        );
    }
}
