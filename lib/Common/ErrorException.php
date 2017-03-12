<?php
namespace Hyperframework\Common;

use ErrorException as Base;

class ErrorException extends Base {
    private $sourceTrace;
    private $sourceTraceStartIndex;

    /**
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @param int $sourceTraceStartIndex
     */
    public function __construct(
        $severity, $message, $file, $line, $sourceTraceStartIndex
    ) {
        parent::__construct($message, 0, $severity, $file, $line);
        $this->sourceTraceStartIndex = (int)$sourceTraceStartIndex;
    }

    /**
     * @return string
     */
    public function getSeverityAsString() {
        return ErrorTypeHelper::convertToString($this->getSeverity());
    }

    /**
     * @return string
     */
    public function getSeverityAsConstantName() {
        return ErrorTypeHelper::convertToConstantName($this->getSeverity());
    }

    /**
     * @return array
     */
    public function getSourceTrace() {
        if ($this->sourceTrace === null) {
            if ($this->sourceTraceStartIndex === 0) {
                $this->sourceTrace = $this->getTrace();
            } else {
                $this->sourceTrace = array_slice(
                    $this->getTrace(), $this->sourceTraceStartIndex
                );
            }
        }
        return $this->sourceTrace;
    }

    /**
     * @return string
     */
    public function getSourceTraceAsString() {
        $trace = $this->getSourceTrace();
        return StackTraceFormatter::format($trace);
    }

    /**
     * @return string
     */
    public function __toString() {
        $result = "exception '" . static::class . "'";
        $message = (string)$this->getMessage();
        if ($message !== '') {
            $result .= " with message '" . $message . "'";
        }
        $result .= ' in ' . $this->getFile() . ':' . $this->getLine()
            . PHP_EOL . 'Stack trace:' . PHP_EOL
            . $this->getSourceTraceAsString();
        return $result;
    }
}
