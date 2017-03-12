<?php
namespace Hyperframework\Common;

class Error {
    private $message;
    private $severity;
    private $file;
    private $line;

    /**
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     */
    public function __construct($severity, $message, $file, $line) {
        $this->severity = $severity;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getSeverity() {
        return $this->severity;
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
     * @return string
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine() {
        return $this->line;
    }

    /**
     * @return bool
     */
    public function isFatal() {
        return ($this->severity & (
            E_USER_ERROR | E_RECOVERABLE_ERROR | E_ERROR
                | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR
        )) !== 0;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getSeverityAsString() . ':  ' . $this->getMessage()
            . ' in ' . $this->getFile() . ' on line ' . $this->getLine();
    }
}
