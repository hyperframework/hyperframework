<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class ErrorExceptionTest extends Base {
    public function test() {
        $exception = new ErrorException(E_NOTICE, 'message', 'file', 0, 1);
        $this->assertSame(E_NOTICE, $exception->getSeverity());
        $this->assertSame('message', $exception->getMessage());
        $this->assertSame('Notice', $exception->getSeverityAsString());
        $this->assertSame('E_NOTICE', $exception->getSeverityAsConstantName());
        $this->assertSame('file', $exception->getFile());
        $this->assertSame(0, $exception->getLine());
        $sourceTrace = array_slice($exception->getTrace(), 1);
        $this->assertSame($sourceTrace, $exception->getSourceTrace());
        $this->assertSame(
            StackTraceFormatter::format($exception->getSourceTrace()),
            $exception->getSourceTraceAsString()
        );
        $this->assertSame(
            'exception \'Hyperframework\Common\ErrorException\' '
                . "with message 'message' in file:0" . PHP_EOL . 'Stack trace:'
                . PHP_EOL . $exception->getSourceTraceAsString(),
            (string)$exception
        );
    }
}
