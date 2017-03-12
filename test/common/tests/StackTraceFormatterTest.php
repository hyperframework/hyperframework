<?php
namespace Hyperframework\Common;

use stdClass;
use Hyperframework\Common\Test\TestCase as Base;

class StackTraceFormatterTest extends Base {
    public function testFormat() {
        $trace = [
            ['function' => 'test'],
            ['function' => 'test2'],
        ];
        $this->assertSame(
            '#0 [internal function]: test()' . PHP_EOL
                . '#1 [internal function]: test2()' . PHP_EOL
                . '#2 {main}',
            StackTraceFormatter::format($trace)
        );
    }

    public function testFormatStackFrame() {
        $this->assertSame(
            'file(0): test()',
            StackTraceFormatter::formatStackFrame(
                ['function' => 'test', 'file' => 'file', 'line' => 0]
            )
        );
    }

    public function testFormatInvocation() {
        $this->assertSame(
            "Class->test('01234567890123\\n...',"
                . " Array, NULL, Object(stdClass))",
            StackTraceFormatter::formatInvocation([
                'class' => 'Class',
                'type' => '->',
                'function' => 'test',
                'args' => ["01234567890123\n\n", [], null, new stdClass]
            ])
        );
    }
}
