<?php
namespace Hyperframework\Common;

use RuntimeException;

class FileAppender {
    /**
     * @param string $path
     * @param string $data
     * @return void
     */
    public static function append($path, $data) {
        FileLock::run($path, 'a', LOCK_EX, function($handle) use ($data) {
            $status = fwrite($handle, $data);
            if ($status !== false) {
                $status = fflush($handle);
            }
            if ($status !== true) {
                throw new RuntimeException("Failed to append file '$path'.");
            }
        });
    }

    /**
     * @param string $path
     * @param string $data
     * @return void
     */
    public static function appendLine($path, $data) {
        $this->append($path, $data . PHP_EOL);
    }
}
