<?php
namespace Hyperframework\Common;

use ReflectionFunction;
use Exception;
use Hyperframework\Common\Test\TestCase as Base;

class ErrorHandlerTest extends Base {
    private $errorReportingBitmask;
    private $shouldLogErrors;
    private $shouldDisplayErrors;
    private $errorLog;
    private $errorPrependString;
    private $errorAppendString;
    private $handler;

    protected function setUp() {
        parent::setUp();
        $this->errorReportingBitmask = error_reporting();
        error_reporting(E_ALL);
        $this->shouldLogErrors = ini_get('log_errors');
        $this->errorLog = ini_get('error_log');
        $this->shouldDisplayErrors = ini_get('display_errors');
        $this->errorPrependString = ini_get('error_prepend_string');
        $this->errorAppendString = ini_get('error_append_string');
        ini_set('log_errors', 1);
        ini_set('error_log', dirname(__DIR__) . '/data/tmp/log');
        ini_set('display_errors', 1);
        ini_set('error_prepend_string', '');
        ini_set('error_append_string', '');
        Config::set(
            'hyperframework.logging.file_handler.path',
            dirname(__DIR__) . '/data/tmp/logger_log'
        );
    }

    protected function tearDown() {
        ini_set('html_errors', 0);
        ini_set('error_log', $this->errorLog);
        ini_set('log_errors', $this->shouldLogErrors);
        ini_set('display_errors', $this->shouldDisplayErrors);
        ini_set('error_prepend_string', $this->errorPrependString);
        ini_set('error_append_string', $this->errorAppendString);
        if ($this->handler !== null) {
            restore_error_handler();
            $this->handler = null;
        }
        if (file_exists(dirname(__DIR__) . '/data/tmp/log')) {
            unlink(dirname(__DIR__) . '/data/tmp/log');
        }
        if (file_exists(dirname(__DIR__) . '/data/tmp/logger_log')) {
            unlink(dirname(__DIR__) . '/data/tmp/logger_log');
        }
        if (file_exists(dirname(__DIR__) . '/log/app.log')) {
            unlink(dirname(__DIR__) . '/log/app.log');
        }
        error_reporting($this->errorReportingBitmask);
        parent::tearDown();
    }

    /**
     * @expectedException Hyperframework\Common\ErrorException
     */
    public function testConvertErrorToException() {
        Config::set(
            'hyperframework.error_handler.error_exception_bitmask', E_ALL
        );
        $this->registerErrorHandler();
        trigger_error('notice');
    }

    public function testHandle() {
        $handler = $this->getMockBuilder('Hyperframework\Common\ErrorHandler')
            ->setMethods(['writeLog'])
            ->getMock();
        $handler->expects($this->once())->method('writeLog');
        $this->callProtectedMethod($handler, 'handle');
    }

    public function testHandleException() {
        $handler = $this->getMockBuilder('Hyperframework\Common\ErrorHandler')
            ->setMethods(['handle'])
            ->getMock();
        $handler->expects($this->once())
            ->method('handle');
        try {
            $this->callPrivateMethod(
                $handler, 'handleException', [new Exception]
            );
            $this->fail();
        } catch (Exception $e) {
        }
    }

    public function testRegisterExceptionHandler() {
        $handler = new ErrorHandler;
        $this->callPrivateMethod($handler, 'registerExceptionHandler');
        $callback = set_exception_handler(function() {});
        restore_exception_handler();
        restore_exception_handler();
        $reflection = new ReflectionFunction($callback);
        $this->assertSame($reflection->getClosureThis(), $handler);
    }

    public function testRegisterErrorHandler() {
        $handler = new ErrorHandler;
        $this->callPrivateMethod($handler, 'registerErrorHandler');
        $callback = set_error_handler(function() {});
        restore_error_handler();
        restore_error_handler();
        $reflection = new ReflectionFunction($callback);
        $this->assertSame($reflection->getClosureThis(), $handler);
    }

    public function testMaxLogLength() {
        Config::set(
            'hyperframework.error_handler.enable_logger', true
        );
        Config::set(
            'hyperframework.error_handler.max_log_length', 1
        );
        ini_set('display_errors', 0);
        $engine = $this->getMockBuilder('Hyperframework\Logging\LoggerEngine')
            ->setMethods(['handle'])->setConstructorArgs(['hyperframework.logging.logger'])->getMock();
        ErrorLogger::setEngine($engine);
        $engine->expects($this->once())
            ->method('handle')->will($this->returnCallback(
                function ($logRecord) {
                    $this->assertSame(1, strlen($logRecord->getMessage()));
                }
            ));
        $this->handleError();
    }

    public function testWriteLogByLogger() {
        Config::set(
            'hyperframework.error_handler.enable_logger', true
        );
        $message = "PHP Notice:  notice in "
            . __FILE__ . " on line 0" . PHP_EOL;
        $this->handleError();
        $log = file_get_contents(dirname(__DIR__) . '/data/tmp/logger_log');
        $this->assertStringEndsWith(
            "[NOTICE] " . getmypid() . " | PHP Notice: "
                . " notice in " . __FILE__ . " on line 0" . PHP_EOL,
            $log
        );
        $this->assertFalse(
            file_exists(dirname(__DIR__) . '/data/tmp/log')
        );
    }

    public function testLoggerIsEnabledByDefault() {
        $message = "PHP Notice: notice in "
            . __FILE__ . " on line 0" . PHP_EOL;
        $this->handleError();
        $this->assertTrue(
            file_exists(dirname(__DIR__) . '/data/tmp/logger_log')
        );
    }

    public function testThrowArgumentErrorException() {
        Config::set(
            'hyperframework.error_handler.error_exception_bitmask', E_ALL
        );
        $this->registerErrorHandler();
        try {
            $function = function($arg) {};
            $function();
        } catch (ErrorException $e) {
            $line = __LINE__ - 2;
            $file = __FILE__;
            $this->assertEquals($e->getLine(), $line);
            $this->assertEquals($e->getFile(), $file);
            return;
        }
        $this->fail();
    }

    private function handleError($handler = null, $error = null) {
        if ($error === null) {
            $error = new Error(E_NOTICE, 'notice', __FILE__, 0);
        }
        if ($handler === null) {
            $handler = $this
                ->getMockBuilder('Hyperframework\Common\ErrorHandler')
                ->setMethods(['getError'])->getMock();
            $handler->method('getError')->willReturn($error);
        }
        $this->callProtectedMethod($handler, 'handle');
    }

    private function registerErrorHandler($handler = null) {
        if ($handler === null) {
            $this->handler = new ErrorHandler;
        } else {
            $this->handler = $handler;
        }
        $this->callPrivateMethod($this->handler, 'registerErrorHandler');
    }
}
