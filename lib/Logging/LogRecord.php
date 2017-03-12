<?php
namespace Hyperframework\Logging;

use DateTime;
use DateTimeZone;

class LogRecord {
    private $level;
    private $message;
    private $time;

    /**
     * @param int $level
     * @param mixed $message
     * @param int|float|DateTime $time
     * @return void
     */
    public function __construct($level, $message, $time = null) {
        if ($time !== null) {
            if (is_int($time)) {
                $timestamp = $time;
                $time = new DateTime;
                $time->setTimestamp($timestamp);
                $this->time = $time;
            } elseif (is_float($time)) {
                $this->time = $this->convertStringToDateTime(
                    sprintf('%.6F', $time)
                );
            } elseif (is_object($time)) {
                $this->time = $time;
            } else {
                $type = gettype($time);
                throw new LoggingException(
                    'Log time must be an object or an integer timestamp or'
                        . " a float timestamp, $type given."
                );
            }
        } else {
            $segments = explode(' ', microtime());
            $this->time = $this->convertStringToDateTime(
                $segments[1] . '.' . (int)($segments[0] * 1000000)
            );
        }
        $this->level = $level;
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @return DateTime
     */
    public function getTime() {
        return $this->time;
    }

    /**
     * @param string
     * @return DateTime
     */
    private function convertStringToDateTime($string) {
        $result = DateTime::createFromFormat('U.u', $string);
        $result->setTimeZone(new DateTimeZone(date_default_timezone_get()));
        return $result;
    }
}
