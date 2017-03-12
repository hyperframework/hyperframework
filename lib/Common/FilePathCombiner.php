<?php
namespace Hyperframework\Common;

class FilePathCombiner {
    /**
     * @param string $a
     * @param string $b
     * @return void
     */
    public static function combine($a, $b) {
        $a = (string)$a;
        $b = (string)$b;
        $separator = '/' === DIRECTORY_SEPARATOR ? '/' : '\/';
        if ($a !== '') {
            $a = rtrim($a, $separator);
            if ($a === '') {
                $a = DIRECTORY_SEPARATOR;
            }
        }
        if ($b !== '') {
            $b = trim($b, $separator);
        }
        if ($b === '') {
            return $a;
        }
        if ($a !== DIRECTORY_SEPARATOR) {
            $a .= DIRECTORY_SEPARATOR;
        }
        return $a . $b;
    }
}
