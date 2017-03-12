<?php
namespace Hyperframework\Web;

use Hyperframework\Web\Test\TestCase as Base;

class ResponseEngineTest extends Base {
    /**
     * @expectedException Hyperframework\Web\CookieException
     */
    public function testInvalidCookieOptionWhenSetCookie() {
        $engine = new ResponseEngine;
        $engine->setCookie('name', 'value', ['invalid' => 'value']);
    }
}
