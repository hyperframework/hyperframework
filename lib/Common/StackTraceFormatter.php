<?php
namespace Hyperframework\Common;

class StackTraceFormatter {
    /**
     * @param array $stackTrace
     * @return string
     */
    public static function format($stackTrace) {
        $result = [];
        $index = 0;
        foreach ($stackTrace as $stackFrame) {
            $result[] = static::formatStackFrame($stackFrame);
            ++$index;
        }
        $result[] = '{main}';
        $message = '';
        $index = 0;
        foreach ($result as $line) {
            if ($index !== 0) {
                $message .= PHP_EOL;
            }
            $message .= '#' . $index . ' ' . $line;
            ++$index;
        }
        return $message;
    }

    /**
     * @param array $stackFrame
     * @return string
     */
    public static function formatStackFrame($stackFrame) {
        $result = '';
        if (isset($stackFrame['file']) === false) {
            $result .= '[internal function]: ';
        } else {
            $result .= $stackFrame['file'] . '(' . $stackFrame['line'] . '): ';
        }
        return $result . static::formatInvocation($stackFrame);
    }

    /**
     * @param array $stackFrame
     * @return string
     */
    public static function formatInvocation($stackFrame) {
        $result = '';
        if (isset($stackFrame['class'])) {
            $result .= $stackFrame['class'] . $stackFrame['type'];
        }
        $result .= $stackFrame['function'];
        $arguments = [];
        if (isset($stackFrame['args'])) {
            foreach ($stackFrame['args'] as $argument) {
                if (is_string($argument)) {
                    if (mb_strlen($argument) > 15) {
                        $argument = mb_substr($argument, 0, 15) . '...';
                    }
                    $argument = str_replace(
                        ["\\", "'", "\n", "\r", "\t", "\v", "\f"],
                        ['\\\\', '\\\'', '\n', '\r', '\t', '\v', '\f'],
                        $argument
                    );
                    $arguments[] = "'$argument'";
                } elseif (is_array($argument)) {
                    $arguments[] = 'Array';
                } elseif (is_null($argument)) {
                    $arguments[] = 'NULL';
                } elseif (is_object($argument)) {
                    $arguments[] = 'Object(' . get_class($argument) . ')';
                } else {
                    $arguments[] = $argument;
                }
            }
        }
        return $result . '(' . implode(', ', $arguments) . ')';
    }
}
