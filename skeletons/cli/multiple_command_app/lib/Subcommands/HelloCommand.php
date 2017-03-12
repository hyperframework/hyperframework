<?php
namespace Subcommands;

use Hyperframework\Cli\Command as Base;

class HelloCommand extends Base {
    public function execute() {
        echo 'hello world!', PHP_EOL;
    }
}
