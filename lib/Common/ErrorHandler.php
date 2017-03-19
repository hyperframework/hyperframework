<?php
namespace Hyperframework\Common;

use Exception;
use Throwable;
use Hyperframework\Logging\LogLevel;

class ErrorHandler {
    private $error;

    /**
     * @return void
     */
    public function run() {
        if (Config::getBool(
            'hyperframework.error_handler.enable_php_error_logging', false
        ) === false) {
            ini_set('log_errors', '0');
        }
        $this->registerErrorHandler();
        $this->registerExceptionHandler();
        $this->registerShutdownHandler();
    }

    /**
     * @return void
     */
    protected function handle() {
        $this->writeLog();
    }

    /**
     * @return void
     */
    protected function writeLog() {
        $logLevel = $this->getLogLevel();
        ErrorLogger::log($logLevel, function() {
            return $this->getLog();
        });
    }

    /**
     * @return int
     */
    protected function getLogLevel() {
        if ($this->getError() instanceof Error) {
            $map = [
                E_DEPRECATED        => LogLevel::NOTICE,
                E_USER_DEPRECATED   => LogLevel::NOTICE,
                E_STRICT            => LogLevel::NOTICE,
                E_NOTICE            => LogLevel::NOTICE,
                E_USER_NOTICE       => LogLevel::NOTICE,
                E_WARNING           => LogLevel::WARNING,
                E_USER_WARNING      => LogLevel::WARNING,
                E_COMPILE_WARNING   => LogLevel::WARNING,
                E_CORE_WARNING      => LogLevel::WARNING,
                E_RECOVERABLE_ERROR => LogLevel::FATAL,
                E_USER_ERROR        => LogLevel::FATAL,
                E_ERROR             => LogLevel::FATAL,
                E_PARSE             => LogLevel::FATAL,
                E_COMPILE_ERROR     => LogLevel::FATAL,
                E_CORE_ERROR        => LogLevel::FATAL
            ];
            return $map[$this->getError()->getSeverity()];
        }
        return LogLevel::FATAL;
    }

    /**
     * @return string
     */
    protected function getLog() {
        $error = $this->getError();
        if ($error instanceof Exception || $error instanceof Throwable) {
            $log = 'Fatal error:  Uncaught ' . $error . PHP_EOL
                . '  thrown in ' . $error->getFile() . ' on line '
                . $error->getLine();
        } else {
            $log = $error;
        }
        $log = 'PHP ' . $log;
        $maxLength = Config::getInt(
            'hyperframework.error_handler.max_log_length'
        );
        if ($maxLength !== null && $maxLength >= 0) {
            return substr($log, 0, $maxLength);
        }
        return $log;
    }

    /**
     * @return object
     */
    protected function getError() {
        return $this->error;
    }

    /**
     * @return void
     */
    private function registerExceptionHandler() {
        set_exception_handler(
            function($exception) {
                $this->handleException($exception);
            }
        );
    }

    /**
     * @return void
     */
    private function registerErrorHandler() {
        set_error_handler(
            function($type, $message, $file, $line) {
                return $this->handleError($type, $message, $file, $line);
            }
        );
    }

    /**
     * @return void
     */
    private function registerShutdownHandler() {
        register_shutdown_function(
            function() {
                $this->handleShutdown();
            }
        );
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    private function handleException($exception) {
        if ($this->getError() === null) {
            $this->error = $exception;
            $this->handle();
        }
        throw $exception;
    }

    /**
     * @param int $type
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    private function handleError($type, $message, $file, $line) {
        if ($this->getError() !== null || (error_reporting() & $type) === 0) {
            return false;
        }
        $sourceTraceStartIndex = 2;
        if ($type === E_WARNING || $type === E_RECOVERABLE_ERROR) {
            $trace = debug_backtrace();
            if (isset($trace[2]) && isset($trace[2]['file'])) {
                $suffix = ', called in ' . $trace[2]['file']
                    . ' on line ' . $trace[2]['line'] . ' and defined';
                if (substr($message, -strlen($suffix)) === $suffix) {
                    $message =
                        substr($message, 0, strlen($message) - strlen($suffix))
                            . " (defined in $file:$line)";
                    $file = $trace[2]['file'];
                    $line = $trace[2]['line'];
                    $sourceTraceStartIndex = 3;
                }
            }
        }
        $errorExceptionBitmask = Config::getInt(
            'hyperframework.error_handler.error_exception_bitmask'
        );
        if ($errorExceptionBitmask === null) {
            $errorExceptionBitmask =
                E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED);
        }
        if (($type & $errorExceptionBitmask) === 0) {
            $this->error = new Error($type, $message, $file, $line);
            $this->handle();
            $this->error = null;
            return false;
        }
        throw new ErrorException(
            $type, $message, $file, $line, $sourceTraceStartIndex
        );
    }

    /**
     * @return void
     */
    private function handleShutdown() {
        if ($this->getError() !== null) {
            return;
        }
        $error = error_get_last();
        if ($error === null || $error['type'] & error_reporting() === 0) {
            return;
        }
        if (in_array($error['type'], [
            E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR
        ])) {
            $this->error = new Error(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
            $this->handle();
        }
    }
}
