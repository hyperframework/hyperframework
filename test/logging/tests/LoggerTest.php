<?php
namespace Hyperframework\Logging;

use stdClass;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Logging\Test\TestCase as Base;

class LoggerTest extends Base {
    /**
     * @dataProvider getShortcutMethods
     */
    public function testShortcutMethods($method) {
        Logger::setLevel(LogLevel::DEBUG);
        if ($method === 'warn') {
            $levelName = 'WARNING';
        } else {
            $levelName = $method;
        }
        $this->mockEngineMethod('log')->will($this->returnCallback(
            function($level, $mixed) use ($levelName) {
                $this->assertSame(LogLevel::getCode($levelName), $level);
                $this->assertSame('message', $mixed);

            })
        );
        Logger::$method('message');
    }

    public function getShortcutMethods() {
        return [
            ['debug'], ['info'], ['warn'], ['notice'], ['error'], ['fatal']
        ];
    }

    public function testLog() {
        $this->mockEngineMethod('log')->will($this->returnCallback(
            function($level, $mixed) {
                $this->assertSame(LogLevel::DEBUG, $level);
                $this->assertSame('message', $mixed);
            })
        );
        Logger::log(LogLevel::DEBUG, 'message');
    }

    public function testSetLevel() {
        $this->mockEngineMethod('setLevel')->with(LogLevel::DEBUG);
        Logger::setLevel(LogLevel::DEBUG);
    }

    public function testGetLevel() {
        $this->mockEngineMethod('getLevel')->willReturn(true);
        $this->assertTrue(Logger::getLevel());
    }

    public function testGetEngine() {
        $this->assertInstanceOf(
            'Hyperframework\Logging\LoggerEngine',
            Logger::getEngine()
        );
    }

    public function testSetEngineUsingConfig() {
        Config::set(
            'hyperframework.logging.logger.engine_class',
            'stdClass'
        );
        $this->assertInstanceOf('stdClass', Logger::getEngine());
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testInvalidEngineConfig() {
        Config::set('hyperframework.logging.logger.engine_class', 'Unknown');
        Logger::getEngine();
    }

    public function testSetEngine() {
        $engine = new stdClass;
        Logger::setEngine($engine);
        $this->assertSame($engine, Logger::getEngine());
        $this->assertSame(
            $engine, Registry::get('hyperframework.logging.logger.engine')
        );
    }

    private function mockEngineMethod($method) {
        $engine = $this->getMockBuilder('Hyperframework\Logging\LoggerEngine')
            ->setConstructorArgs(['hyperframework.logging.logger'])->getMock();
        Logger::setEngine($engine);
        return $engine->expects($this->once())->method($method);
    }
}
