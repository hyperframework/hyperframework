<?php
namespace Hyperframework\Logging;

class ConsoleLogHandler extends FormattingLogHandler {
    /**
     * @param string $log
     * @return void
     */
    public function handleFormattedLog($log) {
        echo $log;
    }
}
