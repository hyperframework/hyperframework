<?php
namespace Hyperframework\Cli\Test;

use Hyperframework\Cli\Command as Base;

class NoArgumentCommand extends Base {
    public function execute() {
        echo __METHOD__;
    }
}
