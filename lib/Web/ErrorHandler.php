<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;
use Hyperframework\Common\Error;
use Hyperframework\Common\ErrorHandler as Base;

class ErrorHandler extends Base {
    private $startupOutputBufferLevel;

    public function __construct() {
        if ($this->isDebuggerEnabled()) {
            ini_set('display_errors', '0');
            ob_start();
        }
        $this->startupOutputBufferLevel = ob_get_level();
    }

    /**
     * @return void
     */
    protected function handle() {
        $this->writeLog();
        $error = $this->getError();
        if ($error instanceof Error && $error->isFatal() === false) {
            return;
        }
        if ($this->isDebuggerEnabled()) {
            $output = $this->getOutput();
            $this->deleteOutput();
            if (Response::headersSent() === false) {
                $this->rewriteHttpHeaders();
            }
            $this->executeDebugger($output);
        } elseif (Response::headersSent() === false) {
            $this->rewriteHttpHeaders();
            if (Config::getBool('hyperframework.web.error_view.enable', true)) {
                $this->deleteOutput();
                $this->renderErrorView();
            }
        }
    }

    /**
     * @param string $output
     * @return void
     */
    protected function executeDebugger($output) {
        $class = Config::getClass(
            'hyperframework.web.debugger.class', Debugger::class
        );
        $debugger = new $class;
        $debugger->execute($this->getError(), $output);
    }

    /**
     * @return void
     */
    protected function renderErrorView() {
        $error = $this->getError();
        if ($error instanceof HttpException) {
            $statusCode = $error->getStatusCode();
            $statusReasonPhrase = $error->getStatusReasonPhrase();
        } else {
            $statusCode = 500;
            $statusReasonPhrase = 'Internal Server Error';
        }
        $class = Config::getClass(
            'hyperframework.web.error_view.class', ErrorView::class
        );
        $view = new $class;
        $view->render($statusCode, $statusReasonPhrase, $error);
    }

    /**
     * @return void
     */
    protected function writeLog() {
        $error = $this->getError();
        if ($error instanceof HttpException) {
            $statusCode = (string)$error->getStatusCode();
            if (strlen($statusCode) !== 0 && $statusCode[0] !== '5') {
                return;
            }
        }
        parent::writeLog();
    }
 
    /**
     * @return string
     */
    protected function getOutput() {
        $this->flushInnerOutputBuffer();
        $content = ob_get_contents();
        if ($content === false) {
            return;
        }
        return $content;
    }

    /**
     * @return void
     */
    protected function deleteOutput() {
        $level = ob_get_level();
        $startupLevel = $this->startupOutputBufferLevel;
        while ($level >= $startupLevel) {
            if ($startupLevel === $level) {
                if ($level !== 0) {
                    ob_clean();
                }
            } else {
                ob_end_clean();
            }
            --$level;
        }
    }

    /**
     * @return void
     */
    private function flushInnerOutputBuffer() {
        $level = ob_get_level();
        $startupLevel = $this->startupOutputBufferLevel;
        if ($level < $startupLevel) {
            return;
        }
        while ($level > $startupLevel) {
            ob_end_flush();
            --$level;
        }
    }

    /**
     * @return void
     */
    private function rewriteHttpHeaders() {
        Response::removeHeaders();
        $error = $this->getError();
        if ($error instanceof HttpException) {
            Response::setStatusCode($error->getStatusCode());
            foreach ($error->getHttpHeaders() as $header) {
                Response::setHeader($header);
            }
        } else {
            Response::setStatusCode(500);
        }
    }

    /**
     * @return bool
     */
    private function isDebuggerEnabled() {
        return Config::getBool('hyperframework.web.debugger.enable', false);
    }
}
