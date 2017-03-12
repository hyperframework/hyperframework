<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class RegistryTest extends Base {
    public function test() {
        Registry::set('name', 'value');
        $this->assertSame('value', Registry::get('name'));
        Registry::remove('name');
        $this->assertFalse(Registry::has('name'));
        Registry::set('name', 'value');
        Registry::clear();
        $this->assertFalse(Registry::has('name'));
    }
}
