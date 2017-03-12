<?php
namespace Hyperframework\Cli;

use ReflectionFunction;
use Hyperframework\Cli\Test\TestCase as Base;

class DefaultArgumentConfigTest extends Base {
    public function test() {
        $reflectionFunction = new ReflectionFunction(function(...$args) {});
        $reflectionParameter = $reflectionFunction->getParameters()[0];
        $config = new DefaultArgumentConfig($reflectionParameter);
        $this->assertSame('arg', $config->getName());
        $this->assertFalse($config->isRequired());
        $this->assertTrue($config->isRepeatable());
    }

    public function testNameIncludesMultipleWords() {
        $reflectionFunction = new ReflectionFunction(function($arg__arg) {});
        $reflectionParameter = $reflectionFunction->getParameters()[0];
        $config = new DefaultArgumentConfig($reflectionParameter);
        $this->assertSame('arg-arg', $config->getName());
    }

    public function testNameOfRepeatableArgumentEqualsList() {
        $reflectionFunction = new ReflectionFunction(
            function(...$list) {}
        );
        $reflectionParameter = $reflectionFunction->getParameters()[0];
        $config = new DefaultArgumentConfig($reflectionParameter);
        $this->assertSame('element', $config->getName());
    }

    public function testNameOfRepeatableArgumentEndsWithList() {
        $reflectionFunction = new ReflectionFunction(
            function(...$argList) {}
        );
        $reflectionParameter = $reflectionFunction->getParameters()[0];
        $config = new DefaultArgumentConfig($reflectionParameter);
        $this->assertSame('arg', $config->getName());
    }
}
