<?php
namespace Hyperframework\Common;

use RuntimeException;

class FileAppender {
    /**
     * @param string $data
     * @return void
     */
    public function append($data) {
        FileLock::run(
            $this->getPath(),
            'a',
            LOCK_EX,
            function($handle) use ($data) {
                $status = fwrite($handle, $data);
                if ($status !== false) {
                    $status = fflush($handle);
                }
                if ($status !== true) {
                    throw new RuntimeException(
                        "Failed to append file '{$this->getPath()}'."
                    );
                }
            }
        );
    }

    /**
     * @param string $data
     * @return void
     */
    public function appendLine($data) {
        $this->append($data . PHP_EOL);
    }

    /**
     * @param string $path
     * @return void
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }
}
