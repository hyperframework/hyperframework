<?php
namespace Hyperframework\Db\Test;

class DbOperationProfileHandler {
    private static $delegate;

    public function handle(array $profile) {
        self::$delegate->handle($profile);
    }

    public static function setDelegate($delegate) {
        self::$delegate = $delegate;
    }
}
