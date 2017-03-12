<?php
namespace Hyperframework\Cli\Test;

use Hyperframework\Cli\Command as Base;

class Command extends Base {
    public function execute($arg) {
        echo __METHOD__;
    }
}
