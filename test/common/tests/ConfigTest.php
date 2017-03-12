<?php
namespace Hyperframework\Common;

use stdClass;
use Hyperframework\Common\Test\TestCase as Base;

class ConfigTest extends Base {
    public function testGet() {
        $this->mockEngineMethod('get')->with('name', 'default')
            ->willReturn(true);
        $this->assertTrue(Config::get('name', 'default'));
    }

    public function testGetString() {
        $this->mockEngineMethod('getString')->with('name', 'default')
            ->willReturn(true);
        $this->assertTrue(Config::getString('name', 'default'));
    }

    public function testGetInt() {
        $this->mockEngineMethod('getInt')->with('name', 'default')
            ->willReturn(true);
        $this->assertTrue(Config::getInt('name', 'default'));
    }

    public function testGetFloat() {
        $this->mockEngineMethod('getFloat')->with('name', 'default')
            ->willReturn(true);
        $this->assertTrue(Config::getFloat('name', 'default'));
    }

    public function testGetArray() {
        $this->mockEngineMethod('getArray')->with('name', 'default')
            ->willReturn(true);
        $this->assertTrue(Config::getArray('name', 'default'));
    }

    public function testGetAppRootPath() {
        $this->mockEngineMethod('getAppRootPath')
            ->willReturn(true);
        $this->assertTrue(Config::getAppRootPath());
    }

    public function testGetAppRootNamespace() {
        $this->mockEngineMethod('getAppRootNamespace')->willReturn(true);
        $this->assertTrue(Config::getAppRootNamespace());
    }

    public function testGetAll() {
        $this->mockEngineMethod('getAll')->willReturn(true);
        $this->assertTrue(Config::getAll());
    }

    public function testSet() {
        $this->mockEngineMethod('set')->with('name', 'value');
        Config::set('name', 'value');
    }

    public function testHas() {
        $this->mockEngineMethod('has')->with('name')->willReturn(true);
        $this->assertTrue(Config::has('name'));
    }

    public function testRemove() {
        $this->mockEngineMethod('remove')->with('name');
        Config::remove('name');
    }

    public function testimport() {
        $this->mockenginemethod('import')->with([]);
        Config::import([]);
    }

    public function testimportFile() {
        $this->mockenginemethod('importFile')->with('path');
        Config::importFile('path');
    }

    public function testGetEngine() {
        $this->assertInstanceOf(
            'Hyperframework\Common\ConfigEngine', Config::getEngine()
        );
    }

    public function testSetEngine() {
        $engine = new stdClass;
        Config::setEngine($engine);
        $this->assertSame(
            $engine, Registry::get('hyperframework.config_engine')
        );
    }

    private function mockEngineMethod($method) {
        $engine = $this->getMock('Hyperframework\Common\ConfigEngine');
        Config::setEngine($engine);
        return $engine->expects($this->once())->method($method);
    }
}
