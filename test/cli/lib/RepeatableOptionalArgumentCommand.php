<?php
namespace Hyperframework\Cli\Test;

use Hyperframework\Cli\Command as Base;

class RepeatableOptionalArgumentCommand extends Base {
    public function execute($arg, ...$arg2) {
        echo __METHOD__;
    }
}
