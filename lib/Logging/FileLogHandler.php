<?php
namespace Hyperframework\Logging;

use Hyperframework\Common\Config;
use Hyperframework\Common\FileAppender;

class FileLogHandler extends FormattingLogHandler {
    private $path;

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
        if ($this->path === null) {
            $this->path = Config::getString(
                $this->getName() . '.path', Config::getString(
                    'hyperframework.logging.file_handler.path',
                    'log' . DIRECTORY_SEPARATOR . 'app.log'
                )
            );
        }
        return $this->path;
    }

    /**
     * @param string $log
     * @return void
     */
    protected function handleFormattedLog($log) {
        $appender = new FileAppender;
        $appender->setPath($this->getPath());
        $appender->append($log);
    }
}
