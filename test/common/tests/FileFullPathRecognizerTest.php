<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;

class FileFullPathRecognizerTest extends Base {
    public function testEmptyPath() {
        $this->assertFalse(FileFullPathRecognizer::isFullPath(''));
    }

    public function testFullPath() {
        if (DIRECTORY_SEPARATOR === '/') {
            $this->assertTrue(FileFullPathRecognizer::isFullPath('/path'));
        } else {
            $this->assertTrue(FileFullPathRecognizer::isFullPath('c:\path'));
            $this->assertTrue(FileFullPathRecognizer::isFullPath('/path'));
            $this->assertTrue(FileFullPathRecognizer::isFullPath('\path'));
        }
    }

    public function testRelativePath() {
        $this->assertFalse(FileFullPathRecognizer::isFullPath('x'));
    }
}
