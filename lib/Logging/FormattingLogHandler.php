<?php
namespace Hyperframework\Logging;

use Hyperframework\Common\Config;

abstract class FormattingLogHandler extends LogHandler {
    private $formatter;

    /**
     * @param LogRecord $record
     * @return void
     */
    public function handle($record) {
        $formatter = $this->getFormatter();
        $formattedLog = $formatter->format($record);
        $this->handleFormattedLog($formattedLog);
    }

    /**
     * @param string $log
     * @return void
     */
    abstract protected function handleFormattedLog($log);

    /**
     * @return LogFormatter
     */
    protected function getFormatter() {
        if ($this->formatter === null) {
            $class = Config::getClass(
                $this->getName() . '.formatter.class',
                Config::getClass(
                    'hyperframework.logging.formatter.class',
                    LogFormatter::class
                )
            );
            $this->formatter = new $class($this->getName() . '.formatter');
        }
        return $this->formatter;
    }
}
