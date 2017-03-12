<?php
namespace Hyperframework\Logging;

class CompositeLogHandler extends LogHandler {
    private $handlers = [];

    /**
     * @param LogHandler $handler
     * @return void
     */
    public function addHandler($handler) {
        $this->handlers[] = $handler;
    }

    /**
     * @param LogRecord $record
     * @return void
     */
    public function handle($record) {
        foreach ($this->handlers as $handler) {
            $handler->handle($record);
        }
    }
}
