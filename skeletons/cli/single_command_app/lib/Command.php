<?php
use Hyperframework\Cli\Command as Base;

class Command extends Base {
    public function execute() {
        echo 'hello world!', PHP_EOL;
    }
}
