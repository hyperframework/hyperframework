<?php
namespace Hyperframework\Cli\Test\Subcommands;

class ChildCommand {
    public function execute($arg) {
        echo __METHOD__;
    }
}
