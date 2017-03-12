<?php
namespace Hyperframework\Common;

use Exception;
use stdClass;
use Hyperframework\Common\Test\Message;
use Hyperframework\Common\Test\TestCase as Base;

class ConfigEngineTest extends Base {
    public function testGet() {
        $engine = new ConfigEngine;
        $engine->set('name', 'value');
        $this->assertSame('value', $engine->get('name'));
    }

    public function testGetReturnDefaultValue() {
        $engine = new ConfigEngine;
        $this->assertSame('default', $engine->get('name', 'default'));
    }

    public function getString() {
        $engine = new ConfigEngine;
        $engine->set('name', 'value');
        $this->assertSame('value', $engine->getString('name'));
    }

    public function testGetStringWhenValueIsInt() {
        $engine = new ConfigEngine;
        $engine->set('name', 1);
        $this->assertSame('1', $engine->getString('name'));
    }

    public function testGetStringWhenValueIsResource() {
        $engine = new ConfigEngine;
        $resource = fopen('php://input', 'r');
        $engine->set('name', $resource);
        try {
            $this->assertTrue(is_string($engine->getString('name')));
        } catch (Exception $e) {
            fclose($resource);
            throw $e;
        }
        fclose($resource);
    }

    public function testGetStringWhenValueIsObject() {
        $engine = new ConfigEngine;
        $engine->set('name', new Message);
        $this->assertSame('message', $engine->getString('name'));
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetStringWhenValueIsInvalidObject() {
        $engine = new ConfigEngine;
        $engine->set('name', new stdClass);
        $engine->getString('name');
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetStringWhenValueIsArray() {
        $engine = new ConfigEngine;
        $engine->set('name', []);
        $engine->getString('name');
    }

    public function testGetStringReturnDefaultValue() {
        $engine = new ConfigEngine;
        $this->assertSame('default', $engine->getString('name', 'default'));
    }

    public function testgetBool() {
        $engine = new ConfigEngine;
        $engine->set('name', true);
        $this->assertTrue($engine->getBool('name'));
    }

    public function testgetBoolReturnDefaultValue() {
        $engine = new ConfigEngine;
        $this->assertTrue($engine->getBool('name', true));
    }

    public function testgetBoolWhenValueIsInt() {
        $engine = new ConfigEngine;
        $engine->set('name', 1);
        $this->assertTrue($engine->getBool('name'));
    }

    public function testGetInt() {
        $engine = new ConfigEngine;
        $engine->set('name', 1);
        $this->assertSame(1, $engine->getInt('name'));
    }

    public function testGetIntReturnDefaultValue() {
        $engine = new ConfigEngine;
        $this->assertSame(1, $engine->getInt('name', 1));
    }

    public function testGetIntWhenValueIsBoolean() {
        $engine = new ConfigEngine;
        $engine->set('name', true);
        $this->assertSame(1, $engine->getInt('name'));
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetIntWhenValueIsObject() {
        $engine = new ConfigEngine;
        $engine->set('name', new stdClass);
        $engine->getInt('name');
    }

    public function testGetFloat() {
        $engine = new ConfigEngine;
        $engine->set('name', 1);
        $this->assertSame(1.0, $engine->getFloat('name'));
    }

    public function testGetFloatReturnDefaultValue() {
        $engine = new ConfigEngine;
        $this->assertSame(1.0, $engine->getFloat('name', 1.0));
    }

    public function testGetFloatWhenValueIsBoolean() {
        $engine = new ConfigEngine;
        $engine->set('name', true);
        $this->assertSame(1.0, $engine->getFloat('name'));
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetFloatWhenValueIsObject() {
        $engine = new ConfigEngine;
        $engine->set('name', new stdClass);
        $engine->getFloat('name');
    }

    public function testGetArray() {
        $engine = new ConfigEngine;
        $engine->set('name', []);
        $this->assertSame([], $engine->getArray('name'));
    }

    public function testGetArrayReturnDefaultValue() {
        $engine = new ConfigEngine;
        $this->assertSame([], $engine->getArray('name', []));
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetArrayWhenValueIsBoolean() {
        $engine = new ConfigEngine;
        $engine->set('name', true);
        $engine->getArray('name');
    }

    public function testGetAppRootPath() {
        $engine = new ConfigEngine;
        $engine->set('hyperframework.app_root_path', '/path');
        $this->assertSame('/path', $engine->getAppRootPath());
    }

    public function testChangeAppRootPath() {
        $engine = new ConfigEngine;
        $engine->set('hyperframework.app_root_path', '/path');
        $this->assertSame('/path', $engine->getAppRootPath());
        $engine->set('hyperframework.app_root_path', '/path2');
        $this->assertSame('/path2', $engine->getAppRootPath());
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testGetAppRootPathWhenConfigNotFound() {
        $engine = new ConfigEngine;
        $engine->getAppRootPath();
    }

    public function testGetAppRootNamespace() {
        $engine = new ConfigEngine;
        $this->assertSame('', $engine->getAppRootNamespace());
    }

    public function testGetAll() {
        $engine = new ConfigEngine;
        $this->assertSame([], $engine->getAll());
    }

    public function testSet() {
        $engine = new ConfigEngine;
        $engine->set('name', 'value');
        $this->assertSame('value', $engine->get('name'));
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testSetByInvalidName() {
        $engine = new ConfigEngine;
        $engine->set('.name', 'value');
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testSetByNameEndsWithDot() {
        $engine = new ConfigEngine;
        $engine->set('name.', 'value');
    } 

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testSetByEmptyName() {
        $engine = new ConfigEngine;
        $engine->set('', 'value');
    } 

    public function testHas() {
        $engine = new ConfigEngine;
        $this->assertFalse($engine->has('name'));
    }

    public function testRemove() {
        $engine = new ConfigEngine;
        $engine->set('name', 'value');
        $engine->remove('name');
        $this->assertFalse($engine->has('name'));
    }

    public function testImport() {
        $engine = new ConfigEngine;
        $engine->import([
            'section' => ['name' => 'value'] 
        ]);
        $this->assertSame('value', $engine->get('section.name'));
    }

    public function testImportNestedSection() {
        $engine = new ConfigEngine;
        $engine->import([
            'section1' => ['section2' => ['name' => 'value']]
        ]);
        $this->assertSame('value', $engine->get('section1.section2.name'));
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testImportWithInvalidSectionFormat() {
        $engine = new ConfigEngine;
        $engine->import(['.section' => 'name']);
    }

    public function testImportFile() {
        $engine = new ConfigEngine;
        $engine->importFile('init.php');
        $this->assertSame('value', $engine->get('key'));
    }

    public function testImportEmptyFile() {
        $engine = new ConfigEngine;
        $engine->importFile('empty.php');
        $this->assertSame([], $engine->getAll());
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testImportInvalidFile() {
        $engine = new ConfigEngine;
        $engine->importFile('invalid.php');
    }
}
