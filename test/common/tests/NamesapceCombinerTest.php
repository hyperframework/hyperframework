<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class NamesapceCombinerTest extends Base {
    public function testCombine() {
        $name = 'Namespace';
        $this->assertSame(
            'Namespace\Class', NamespaceCombiner::combine($name, 'Class')
        );
    }

    public function testCombineRootNamespace() {
        $name= '\\';
        $name = NamespaceCombiner::combine($name, 'Class');
        $this->assertSame('\Class', $name);
    }

    public function testCombineNamespaceWhichEndsWithNamespaceSeparator() {
        $name = 'Namespace\\';
        $name = NamespaceCombiner::combine($name, 'Class');
        $this->assertSame('Namespace\Class', $name);
    }

    public function testCombineEmpty() {
        $name = 'Class';
        $name = NamespaceCombiner::combine($name, null);
        $this->assertSame('Class', $name);
    }
}
