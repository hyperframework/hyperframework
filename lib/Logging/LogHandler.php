<?php
namespace Hyperframework\Logging;

abstract class LogHandler {
    private $name;

    /**
     * @param LogRecord $record
     * @return void
     */
    abstract public function handle($record);

    /**
     * @param string $text
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}
