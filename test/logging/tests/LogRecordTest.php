<?php
namespace Hyperframework\Logging;

use DateTime;
use Hyperframework\Logging\Test\TestCase as Base;

class LogRecordTest extends Base {
    public function testDefaultTime() {
        $record = new LogRecord(LogLevel::ERROR, 'message');
        $this->assertTrue($record->getTime() instanceof DateTime);
    }

    public function testCustomIntegerTimestamp() {
        $time = time();
        $record = new LogRecord(LogLevel::ERROR, null, $time);
        $this->assertSame(
            date('Y-m-d H:i:s', $time),
            $record->getTime()->format('Y-m-d H:i:s')
        );
    }

    public function testCustomFloatTimestamp() {
        $time = microtime(true);
        $record = new LogRecord(LogLevel::ERROR, null, $time);
        $this->assertSame(
            sprintf('%.6F', $time), $record->getTime()->format('U.u')
        );
    }

    public function testCustomDateTime() {
        $time = new DateTime;
        $record = new LogRecord(LogLevel::ERROR, null, $time);
        $this->assertSame($time, $record->getTime());
    }

    /**
     * @expectedException Hyperframework\Logging\LoggingException
     */
    public function testInvalidCustomTime() {
        $record = new LogRecord(LogLevel::ERROR, null, 'invalid time');
    }

    public function testLevel() {
        $record = new LogRecord(LogLevel::ERROR, null);
        $this->assertSame(LogLevel::ERROR, $record->getLevel());
    }

    public function testMessage() {
        $record = new LogRecord(LogLevel::ERROR, 'message');
        $this->assertSame('message', $record->getMessage());
    }
}
