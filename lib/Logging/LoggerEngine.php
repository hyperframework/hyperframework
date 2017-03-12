<?php
namespace Hyperframework\Logging;

use Closure;
use Hyperframework\Common\Config;
use Hyperframework\Common\ConfigException;

class LoggerEngine {
    private $name;
    private $level;
    private $handler;

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * @param int $level
     * @param mixed $mixed
     * @return void
     */
    public function log($level, $mixed) {
        if ($level > $this->getLevel()) {
            return;
        }
        if ($mixed instanceof Closure) {
            $data = $mixed();
        } else {
            $data = $mixed;
        }
        if (is_array($data)) {
            $message = isset($data['message']) ? $data['message'] : null;
            $time = isset($data['time']) ? $data['time'] : null;
            $record = new LogRecord($level, $message, $time);
        } else {
            $record = new LogRecord($level, $data);
        }
        $this->handle($record);
    }

    /**
     * @param int $level
     * @return void
     */
    public function setLevel($level) {
        $this->level = $level;
    }

    /**
     * @return int
     */
    public function getLevel() {
        if ($this->level === null) {
            $configName = $this->getName() . '.level';
            $name = Config::getString($configName);
            if ($name === null) {
                $configName = 'hyperframework.logging.level';
                $name = Config::getString($configName);
            }
            if ($name !== null) {
                $level = LogLevel::getCode($name);
                if ($level === null) {
                    throw new ConfigException(
                        "Log level '$name' is invalid, set using config "
                            . "'$configName'. The available log levels are: "
                            . "DEBUG, INFO, NOTICE, WARNING, ERROR, FATAL, OFF."
                    );
                }
                $this->level = $level;
            } else {
                $this->level = LogLevel::INFO;
            }
        }
        return $this->level;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param LogRecord $record
     * @return void
     */
    protected function handle($record) {
        $handler = $this->getHandler();
        $handler->handle($record);
    }

    /**
     * @return LogHandler
     */
    protected function getHandler() {
        if ($this->handler === null) {
            $config = Config::getArray($this->getName() . '.handlers');
            if ($config === null) {
                $config = Config::getClass($this->getName() . '.handler.class');
            } else {
                if (Config::has($this->getName() . '.handler')) {
                    throw new ConfigException(
                        "Config '" . $this->getName()
                            . ".handler.*' conflict with '"
                            . $this->getName() . ".handlers.*'."
                    );
                }
                $config = array_keys($config);
            }
            if ($config === null) {
                $config = Config::getArray('hyperframework.logging.handlers');
                if ($config === null) {
                    $config = Config::getClass(
                        'hyperframework.logging.handler.class',
                        FileLogHandler::class
                    );
                } else {
                    if (Config::has('hyperframework.logging.handler')) {
                        throw new ConfigException(
                            "Config 'hyperframework.logging.handler.*'"
                                . " conflict with 'hyperframework.logging"
                                . ".handlers.*'."
                        );
                    }
                    $config = array_keys($config);
                }
            }
            if (is_array($config)) {
                $this->handler = new CompositeLogHandler(null);
                foreach ($config as $name) {
                    $class = Config::getClass(
                        $this->getName() . '.handlers.' . $name . '.class',
                        Config::getClass(
                            'hyperframework.logging.handler.class',
                            FileLogHandler::class
                        )
                    );
                    $this->handler->addHandler(
                        new $class($this->getName() . '.handlers.' . $name)
                    );
                }
            } else {
                $this->handler = new $config($this->getName() . '.handler');
            }
        }
        return $this->handler;
    }
}
