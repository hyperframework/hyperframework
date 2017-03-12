<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class ErrorTypeHelperTest extends Base {
    public function testConvertToString() {
        $this->assertSame(
            'Error', ErrorTypeHelper::convertToString(E_USER_ERROR)
        );
    }

    public function testConvertToConstantName() {
        $this->assertSame(
            'E_USER_ERROR', ErrorTypeHelper::convertToConstantName(E_USER_ERROR)
        );
    }
}
