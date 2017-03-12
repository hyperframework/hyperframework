<?php
namespace Hyperframework\Common;

class EventEmitter {
    /**
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public static function bind($name, $callback) {
        static::getEngine()->bind($name, $callback);
    }

    /**
     * @param array $bindings
     * @return void
     */
    public static function bindAll($bindings) {
        static::getEngine()->bindAll($bindings);
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public static function unbind($name, $callback) {
        static::getEngine()->unbind($name, $callback);
    }

    /**
     * @param array $bindings
     * @return void
     */
    public static function unbindAll($bindings) {
        static::getEngine()->unbindAll($bindings);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public static function emit($name, $arguments = []) {
        static::getEngine()->emit($name, $arguments);
    }

    /**
     * @return EventEmitterEngine
     */
    public static function getEngine() {
        return Registry::get('hyperframework.event_emitter_engine', function() {
            $class = Config::getClass(
                'hyperframework.event_emitter_engine_class',
                EventEmitterEngine::class
            );
            return new $class;
        });
    }

    /**
     * @param EventEmitterEngine $engine
     * @return void
     */
    public static function setEngine($engine) {
        Registry::set('hyperframework.event_emitter_engine', $engine);
    }
}
