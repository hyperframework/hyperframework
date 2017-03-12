<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class InflectorTest extends Base {
    public function testPluralize() {
        $this->assertSame('products', Inflector::pluralize('product'));
    }

    public function testPluralizeSpecialWord() {
        $this->assertSame('mice', Inflector::pluralize('mouse'));
    }

    public function testSingularize() {
        $this->assertSame('product', Inflector::singularize('products'));
    }

    public function testSingularizeSpecialWord() {
        $this->assertSame('mouse', Inflector::singularize('mice'));
    }

    public function testPluralizeEmpty() {
        $this->assertSame('', Inflector::pluralize(''));
    }

    public function testPluralizeUpperCaseWord() {
        $this->assertSame('PRODUCTS', Inflector::pluralize('PRODUCT'));
    }

    public function testPluralizeWordWhichFirstLetterIsCapitalLetter() {
        $this->assertSame('Products', Inflector::pluralize('Product'));
    }
}
