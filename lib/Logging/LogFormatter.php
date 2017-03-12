<?php
namespace Hyperframework\Logging;

use Hyperframework\Common\Config;

class LogFormatter {
    private $name;

    /**
     * @param string $text
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * @param LogRecord $record
     * @return string
     */
    public function format($record) {
        $time = $record->getTime();
        $result = $time->format(Config::getString(
            $this->getName() . '.time_format', Config::getString(
                'hyperframework.logging.formatter.time_format',
                'Y-m-d H:i:s'
            )
        ));
        if (Config::getBool(
            $this->getName() . '.include_level', Config::getBool(
                'hyperframework.logging.formatter.include_level',
                true
            )
        )) {
            $result .= ' [' . LogLevel::getName($record->getLevel()) . ']';
        }
        if (Config::getBool(
            $this->getName() . '.include_pid', Config::getBool(
                'hyperframework.logging.formatter.include_pid',
                true
            )
        )) {
            $result .= ' ' . getmypid();
        }
        $message = (string)$record->getMessage();
        if ($message !== '') {
            $result .= ' | ' . str_replace("\n", "\n  ", $message);
        }
        return $result . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}
