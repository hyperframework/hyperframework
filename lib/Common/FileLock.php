<?php
namespace Hyperframework\Common;

use Closure;
use RuntimeException;

class FileLock {
    /**
     * @param string $filePath
     * @param string $fileMode
     * @param string $lockType
     * @param Closure $callback
     * @return mixed
     */
    public static function run($filePath, $fileMode, $lockType, $callback) {
        $fullPath = FileFullPathBuilder::build($filePath);
        DirectoryMaker::make(dirname($fullPath));
        $handle = fopen($fullPath, $fileMode);
        if ($handle === false) {
            throw new RuntimeException(
                "Failed to open or create file '$fullPath'."
            );
        }
        try {
            if (flock($handle, $lockType) === false) {
                throw new RuntimeException("Failed to lock file '$fullPath'.");
            }
            try {
                return $callback($handle);
            } finally {
                flock($handle, LOCK_UN);
            }
        } finally {
            fclose($handle);
        }
    }
}
