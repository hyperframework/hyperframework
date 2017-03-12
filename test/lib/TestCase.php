<?php
namespace Hyperframework\Test;

use ReflectionClass;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use PHPUnit_Framework_TestCase as Base;

class TestCase extends Base {
    protected function tearDown() {
        Registry::clear();
    }

    protected function callProtectedMethod(
        $objectOrClass, $method, $args = []
    ) {
        return $this->callNonPublicMethod($objectOrClass, $method, $args, true);
    }

    protected function callPrivateMethod($objectOrClass, $method, $args = []) {
        return $this->callNonPublicMethod(
            $objectOrClass, $method, $args, false
        );
    }

    protected function getAppLogFileContent() {
        return file_get_contents(Config::getAppRootPath() . '/log/app.log');
    }

    protected function deleteAppLogFile() {
        $path = Config::getAppRootPath() . '/log/app.log';
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function callNonPublicMethod(
        $objectOrClass, $method, $args = [], $isProtected
    ) {
        $class = $objectOrClass;
        $object = null;
        if (is_object($objectOrClass)) {
            $class = get_class($objectOrClass);
            $object = $objectOrClass;
        }
        $reflectionClass = new ReflectionClass($class);
        $reflectionMethod = $reflectionClass->getMethod($method);
        if ($isProtected) {
            if ($reflectionMethod->isProtected() === false) {
                throw new TestException(
                    $class . '::' . $method . ' is not protected.'
                );
            }
        } else {
            if ($reflectionMethod->isPrivate() === false) {
                throw new TestException(
                    $class . '::' . $method . ' is not private.'
                );
            }
        }
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($object, $args);
    }
}
