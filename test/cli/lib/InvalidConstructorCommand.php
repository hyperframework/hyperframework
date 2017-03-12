<?php
namespace Hyperframework\Cli\Test;

use Hyperframework\Cli\Command as Base;

class ParentConstructorNotCalledCommand extends Base {
    public function __construct() {
    }

    public function execute($arg) {
        echo __METHOD__;
    }
}
