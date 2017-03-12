<?php
namespace Hyperframework\Common;

class NamespaceCombiner {
    /**
     * @param string $a
     * @param string $b
     * @return string
     */
    public static function combine($a, $b) {
        $a = (string)$a;
        $b = (string)$b;
        if ($a !== '') {
            $a = rtrim($a, '\\');
            if ($a === '') {
                $a = '\\';
            }
        }
        if ($b !== '') {
            $b = trim($b, '\\');
        }
        if ($b === '') {
            return $a;
        }
        if ($a !== '\\') {
            $a .= '\\';
        }
        return $a . $b;
    }
}
