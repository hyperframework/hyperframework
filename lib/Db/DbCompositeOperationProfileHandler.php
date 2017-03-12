<?php
namespace Hyperframework\Db;

class DbCompositeOperationProfileHandler {
    private $handlers = [];

    /**
     * @param object $handler
     * @return void
     */
    public function addHandler($handler) {
        $this->handlers[] = $handler;
    }

    /**
     * @param array $profile
     * @return void
     */
    public function handle($profile) {
        foreach ($this->handlers as $handler) {
            $handler->handle($profile);
        }
    }
}
