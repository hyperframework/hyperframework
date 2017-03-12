<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class ExitHelperTest extends Base {
    private $isExitCalled;

    public function testExitScript() {
        $isCalled = false;
        Config::set('hyperframework.exit_function', function() {
            $this->onExit();
        });
        ExitHelper::exitScript();
        $this->assertTrue($this->isExitCalled);
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testInvalidConfig() {
        Config::set('hyperframework.exit_function', true);
        ExitHelper::exitScript();
    }

    public function onExit() {
        $this->isExitCalled = true;
    }
}
