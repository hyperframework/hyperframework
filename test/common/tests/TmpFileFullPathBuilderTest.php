<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class TmpFileFullPathBuilderTest extends Base {
    public function testGetRootPath() {
        $path = $this->callProtectedMethod(
            'Hyperframework\Common\TmpFileFullPathBuilder', 'getRootPath'
        );
        $this->assertSame(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tmp', $path
        );
    }
}
