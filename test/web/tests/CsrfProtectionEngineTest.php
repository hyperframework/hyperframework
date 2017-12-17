<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;
use Hyperframework\Web\Test\TestCase as Base;
use Hyperframework\Common\Registry;

class CsrfProtectionEngineTest extends Base {
    public function testRunWithSafeMethod() {
        $engine2 = $this->getMock('Hyperframework\Web\ResponseEngine');
        $engine2->expects($this->once())->method('setCookie');
        Registry::set('hyperframework.web.response_engine', $engine2);
        $engine = new CsrfProtectionEngine;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $engine->run();
    }

    /**
     * @expectedException Hyperframework\Web\ForbiddenException
     */
    public function testTokenNotInitialized() {
        $engine = new CsrfProtectionEngine;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $engine->run();
    }

    /**
     * @expectedException Hyperframework\Web\ForbiddenException
     */
    public function testInvalidToken() {
        $engine2 = $this->getMock('Hyperframework\Web\ResponseEngine');
        $engine2->expects($this->once())->method('setCookie');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        Registry::set('hyperframework.web.response_engine', $engine2);
        $engine = new CsrfProtectionEngine;
        $_POST = ['_csrf_token' => 'invalid'];
        $_COOKIE['_csrf_token'] = 'token';
        $engine->run();
    }

    public function testChangeTokenNameByConfig() {
        $engine = new CsrfProtectionEngine;
        Config::set('hyperframework.web.csrf_protection.token_name' , 'name');
        $this->assertSame('name', $engine->getTokenName());
    }

    public function testGetDefaultTokenName() {
        $engine = new CsrfProtectionEngine;
        $this->assertSame('_csrf_token', $engine->getTokenName());
    }

    public function testIsSafeMethod() {
        $engine = new CsrfProtectionEngine;
        $this->assertTrue(
            $this->callProtectedMethod($engine, 'isSafeMethod', ['GET'])
        );
    }

    public function testGenerateToken() {
        $engine = new CsrfProtectionEngine;
        $this->assertNotSame(
            $this->callProtectedMethod($engine, 'generateToken'),
            $this->callProtectedMethod($engine, 'generateToken')
        );
        $this->assertSame(
            40, strlen($this->callProtectedMethod($engine, 'generateToken'))
        );
    }
}
