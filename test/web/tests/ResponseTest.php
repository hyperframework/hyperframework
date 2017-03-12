<?php
namespace Hyperframework\Web;

use stdClass;
use Hyperframework\Common\Registry;
use Hyperframework\Common\Config;
use Hyperframework\Web\Test\TestCase as Base;

class ResponseTest extends Base {
    public function testSetHeader() {
        $this->mockEngineMethod('setHeader')->with('Name: value');
        Response::setHeader('Name: value');
    }

    public function testRemoveHeader() {
        $this->mockEngineMethod('removeHeader')->with('name');
        Response::removeHeader('name');
    }

    public function testRemoveHeaders() {
        $this->mockEngineMethod('removeHeaders');
        Response::removeHeaders();
    }

    public function testSetStatusCode() {
        $this->mockEngineMethod('setStatusCode')->with(404);
        Response::setStatusCode(404);
    }

    public function testGetStatusCode() {
        $this->mockEngineMethod('getStatusCode')->willReturn(true);
        $this->assertTrue(Response::getStatusCode());
    }

    public function testSetCookie() {
        $this->mockEngineMethod('setCookie')->with('name', 'value', []);
        Response::setCookie('name', 'value', []);
    }

    public function testHeadersSent() {
        $this->mockEngineMethod('headersSent')->willReturn(true);
        $this->assertTrue(Response::headersSent());
    }

    public function testGetEngine() {
        $this->assertInstanceOf(
            'Hyperframework\Web\ResponseEngine', Response::getEngine()
        );
    }

    public function testSetEngineUsingConfig() {
        Config::set(
            'hyperframework.web.response_engine_class', 'stdClass'
        );
        $this->assertInstanceOf('stdClass', Response::getEngine());
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testInvalidEngineConfig() {
        Config::set('hyperframework.web.response_engine_class', 'Unknown');
        Response::getEngine();
    }

    public function testSetEngine() {
        $engine = new stdClass;
        Response::setEngine($engine);
        $this->assertSame($engine, Response::getEngine());
        $this->assertSame(
            $engine, Registry::get('hyperframework.web.response_engine')
        );
    }

    private function mockEngineMethod($method) {
        $engine = $this->getMock('Hyperframework\Web\ResponseEngine');
        Response::setEngine($engine);
        return $engine->expects($this->once())->method($method);
    }
}
