<?php
namespace Hyperframework\Web;

use stdClass;
use Hyperframework\Common\Registry;
use Hyperframework\Common\Config;
use Hyperframework\Web\Test\TestCase as Base;

class CsrfProtectionTest extends Base {
    public function testRun() {
        $this->mockEngineMethod('run');
        CsrfProtection::run();
    }

    public function testGetToken() {
        $this->mockEngineMethod('getToken')->willReturn(true);
        $this->assertTrue(CsrfProtection::getToken());
    }

    public function testGetTokenName() {
        $this->mockEngineMethod('getTokenName')->willReturn(true);
        $this->assertTrue(CsrfProtection::getTokenName());
    }

    public function testGetEngine() {
        $this->assertInstanceOf(
            'Hyperframework\Web\CsrfProtectionEngine',
            CsrfProtection::getEngine()
        );
    }

    public function testSetEngineUsingConfig() {
        Config::set(
            'hyperframework.web.csrf_protection.engine_class',
            'stdClass'
        );
        $this->assertInstanceOf('stdClass', CsrfProtection::getEngine());
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testInvalidEngineConfig() {
        Config::set(
            'hyperframework.web.csrf_protection.engine_class', 'Unknown'
        );
        CsrfProtection::getEngine();
    }

    public function testSetEngine() {
        $engine = new stdClass;
        CsrfProtection::setEngine($engine);
        $this->assertSame($engine, CsrfProtection::getEngine());
        $this->assertSame(
            $engine, Registry::get('hyperframework.web.csrf_protection_engine')
        );
    }

    public function testIsEnabled() {
        Config::set('hyperframework.web.csrf_protection.enable', false);
        $this->assertFalse(CsrfProtection::isEnabled());
    }

    private function mockEngineMethod($method) {
        $engine = $this->getMock('Hyperframework\Web\CsrfProtectionEngine');
        CsrfProtection::setEngine($engine);
        return $engine->expects($this->once())->method($method);
    }
}
