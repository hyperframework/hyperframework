<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class ErrorTest extends Base {
    public function test() {
        $error = new Error(E_ERROR, 'message', 'file', 0);
        $this->assertSame(E_ERROR, $error->getSeverity());
        $this->assertSame('message', $error->getMessage());
        $this->assertSame('Fatal error', $error->getSeverityAsString());
        $this->assertSame('E_ERROR', $error->getSeverityAsConstantName());
        $this->assertSame('file', $error->getFile());
        $this->assertSame(0, $error->getLine());
        $this->assertSame(
            'Fatal error:  message in file on line 0', (string)$error
        );
    }
}
