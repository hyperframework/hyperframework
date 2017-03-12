<?php
namespace Hyperframework\Common;

class EventEmitterEngine {
    private $callbacks = [];

    /**
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function bind($name, $callback) {
        if (isset($this->callbacks[$name]) === false) {
            $this->callbacks[$name] = [];
        }
        $this->callbacks[$name][] = $callback;
    }

    /**
     * @param array $bindings
     * @return void
     */
    public function bindAll($bindings) {
        foreach ($bindings as $binding) {
            $this->bind($binding['name'], $binding['callback']);
        }
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function unbind($name, $callback) {
        if (isset($this->callbacks[$name])) {
            $index = array_search($callback, $this->callbacks[$name], true);
            if ($index !== false) {
                unset($this->callbacks[$name][$index]);
                if (count($this->callbacks[$name]) === 0) {
                    unset($this->callbacks[$name]);
                }
            }
        }
    }

    /**
     * @param array $bindings
     * @return void
     */
    public function unbindAll($bindings) {
        foreach ($bindings as $binding) {
            $this->unbind($binding['name'], $binding['callback']);
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public function emit($name, $arguments = []) {
        if (isset($this->callbacks[$name])) {
            $callbacks = $this->callbacks[$name];
        } else {
            $callbacks = [];
        }
        if (isset($this->callbacks['hyperframework.event_emitting'])) {
            $event = [
                'name' => $name,
                'arguments' => $arguments,
                'callbacks' => $callbacks
            ];
            foreach (
                $this->callbacks['hyperframework.event_emitting'] as $callback
            ) {
                call_user_func($callback, $event);
            }
        }
        foreach ($callbacks as $callback) {
            call_user_func_array($callback, $arguments);
        }
        if (isset($this->callbacks['hyperframework.event_emitted'])) {
            $event = [
                'name' => $name,
                'arguments' => $arguments,
                'callbacks' => $callbacks
            ];
            foreach (
                $this->callbacks['hyperframework.event_emitted'] as $callback
            ) {
                call_user_func($callback, $event);
            }
        }
    }
}
