<?php
namespace Hyperframework\Logging;

use stdClass;
use Datetime;
use Hyperframework\Common\Config;
use Hyperframework\Logging\Test\TestCase as Base;

class LoggerEngineTest extends Base {
    private $loggerEngine;

    protected function setUp() {
        parent::setUp();
        $this->loggerEngine =
            $this->getMockBuilder('Hyperframework\Logging\LoggerEngine')
                ->setConstructorArgs(['hyperframework.logging.logger'])
                ->setMethods(['handleLogRecord'])->getMock();
    }

    public function testGenerateLogUsingClosure() {
        $this->mockHandleLogRecord(function($logRecord) {
            $this->assertSame(LogLevel::ERROR, $logRecord->getLevel());
            $this->assertSame('message', $logRecord->getMessage());
        });
        $this->loggerEngine->log(LogLevel::ERROR, function() {
            return 'message';
        });
    }

    public function testLogString() {
        $this->mockHandleLogRecord(function($logRecord) {
            $this->assertSame(LogLevel::ERROR, $logRecord->getLevel());
            $this->assertSame('message', $logRecord->getMessage());
        });
        $this->loggerEngine->log(LogLevel::ERROR, 'message');
    }

    public function testLogEmptyArray() {
        $this->mockHandleLogRecord(function($logRecord) {
            $this->assertInstanceOf(
                'Hyperframework\Logging\LogRecord', $logRecord
            );
        });
        $this->loggerEngine->log(LogLevel::ERROR, []);
    }

    public function testLogCustomTime() {
        $time = new DateTime;
        $this->mockHandleLogRecord(function($logRecord) use ($time) {
            $this->assertSame($time, $logRecord->getTime());
        });
        $this->loggerEngine->log(LogLevel::ERROR, ['time' => $time]);
    }

    public function testDefaultLevel() {
        $this->mockHandleLogRecord(function($logRecord) {
            $this->assertSame(LogLevel::INFO, $logRecord->getLevel());
        });
        $this->loggerEngine->log(LogLevel::DEBUG, 'message');
        $this->loggerEngine->log(LogLevel::INFO, 'message');
    }

    public function testChangeLevel() {
        $this->mockHandleLogRecord(function($logRecord) {
            $this->assertSame(LogLevel::ERROR, $logRecord->getLevel());
        });
        $this->loggerEngine->setLevel(LogLevel::ERROR);
        $this->loggerEngine->log(LogLevel::WARNING, 'message');
        $this->loggerEngine->log(LogLevel::ERROR, 'message');
    }

    public function testChangeLevelUsingConfig() {
        $this->mockHandleLogRecord(function($logRecord) {
            $this->assertSame(LogLevel::ERROR, $logRecord->getLevel());
        });
        Config::set('hyperframework.logging.logger.level', 'ERROR');
        $this->loggerEngine->log(LogLevel::WARNING, 'message');
        $this->loggerEngine->log(LogLevel::ERROR, 'message');
    }

    /**
     * @expectedException Hyperframework\Logging\LoggingException
     */
    public function testInvalidTime() {
        $this->loggerEngine->log(LogLevel::ERROR, ['time' => 'invalid']);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidLevelConfig() {
        Config::set('hyperframework.logging.logger.level', 'UNKNOWN');
        $this->loggerEngine->log(LogLevel::ERROR, 'message');
    }

    private function mockHandleLogRecord($handleCallback) {
        $this->loggerEngine->method('handleLogRecord')->will(
            $this->returnCallback($handleCallback)
        );
    }

    public function testDefaultLogHandler() {
        $engine = new LoggerEngine('hyperframework.logging.logger');
        $this->assertTrue(
            $this->callProtectedMethod($engine, 'getHandler')
                instanceof FileLogHandler
        );
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testInvalidLogHandler() {
        Config::set('hyperframework.logging.logger.handler.class', 'Unknown');
        $engine = new LoggerEngine('hyperframework.logging.logger');
        //$handler = new LogHandler;
        $this->callProtectedMethod($engine, 'getHandler');
    }

    public function testCustomLogHandler() {
        Config::set(
            'hyperframework.logging.logger.handler.class',
            'Hyperframework\Logging\Test\CustomLogHandler'
        );
        $engine = new LoggerEngine('hyperframework.logging.logger');
        //$handler = new LogHandler;
        $this->assertTrue(
            $this->callProtectedMethod($engine, 'getHandler')
                instanceof Test\CustomLogHandler
        );
    }
}
