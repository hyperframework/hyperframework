<?php
namespace Hyperframework\Logging;

class ConsoleLogHandler extends FormattedLogHandler {
    /**
     * @param string $log
     * @return void
     */
    public function handleFormattedLog($log) {
        echo $log;
    }
}
