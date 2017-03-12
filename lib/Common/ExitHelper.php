<?php
namespace Hyperframework\Common;

class ExitHelper {
    /**
     * @param int $status
     * @return void
     */
    public static function exitScript($status = 0) {
        $exitFunction = Config::getCallable('hyperframework.exit_function');
        if ($exitFunction === null) {
            exit($status);
        } else {
            call_user_func($exitFunction, $status);
        }
    }
}
