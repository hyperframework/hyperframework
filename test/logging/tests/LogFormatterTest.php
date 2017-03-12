<?php
namespace Hyperframework\Logging;

use Hyperframework\Logging\Test\TestCase as Base;

class LogFormatterTest extends Base {
    private $time;

    protected function setUp() {
        $this->time = time();
    }

    public function testLogWithMessage() {
        $this->assertSame(
            $this->getLogPrefix() . ' | message' . PHP_EOL,
            $this->getFormattedText('message')
        );
    }

    public function testLogWithoutMessage() {
        $log = $this->getLogPrefix() . PHP_EOL;
        $this->assertSame($log, $this->getFormattedText(null));
        $this->assertSame($log, $this->getFormattedText(''));
    }

    private function getLogPrefix() {
        return date("Y-m-d H:i:s", $this->time) . ' [ERROR] ' . getmypid();
    }

    private function getFormattedText($message) {
        $formatter = new LogFormatter('');
        $record = new LogRecord(LogLevel::ERROR, $message, $this->time);
        return $formatter->format($record);
    }
}
