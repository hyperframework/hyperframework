<?php
namespace Hyperframework\Web;

use stdClass;
use Hyperframework\Common\Registry;
use Hyperframework\Common\Config;
use Hyperframework\Web\Test\TestCase as Base;

class RequestTest extends Base {
    public function testSetHeader() {
        $this->mockEngineMethod('getHeaders');
        Request::getHeaders();
    }

    public function testOpenInputStream() {
        $this->mockEngineMethod('openInputStream');
        Request::openInputStream();
    }

    public function testGetEngine() {
        $this->assertInstanceOf(
            'Hyperframework\Web\RequestEngine',
            Request::getEngine()
        );
    }

    public function testSetEngineUsingConfig() {
        Config::set(
            'hyperframework.web.request_engine_class',
            'stdClass'
        );
        $this->assertInstanceOf('stdClass', Request::getEngine());
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testInvalidEngineConfig() {
        Config::set('hyperframework.web.request_engine_class', 'Unknown');
        Request::getEngine();
    }

    private function mockEngineMethod($method) {
        $engine = $this->getMock('Hyperframework\Web\RequestEngine');
        Registry::set('hyperframework.web.request_engine', $engine);
        return $engine->expects($this->once())->method($method);
    }
}
