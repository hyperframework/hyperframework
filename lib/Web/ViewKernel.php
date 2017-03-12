<?php
namespace Hyperframework\Web;

use ArrayAccess;
use InvalidArgumentException;
use Closure;
use Hyperframework\Common\Config;
use Hyperframework\Common\FileFullPathBuilder;
use Hyperframework\Common\FileFullPathRecognizer;
use Hyperframework\Common\FilePathCombiner;

abstract class ViewKernel implements ArrayAccess {
    private $viewModel;
    private $loadFileFunction;
    private $blocks = [];
    private $layoutPathStack = [];
    private $rootPath;
    private $file;
    private $layoutPath;

    /**
     * @param array $viewModel
     */
    public function __construct($viewModel = null) {
        $this->loadFileFunction = Closure::bind(function () {
            require $this->getFile();
        }, $this, null);
        $this->viewModel = $viewModel === null ? [] : $viewModel;
    }

    /**
     * @param string $path
     * @return void
     */
    public function render($path) {
        $path = (string)$path;
        if ($path === '') {
            throw new ViewException('The view path cannot be empty.');
        }
        if (DIRECTORY_SEPARATOR !== '/') {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        }
        if (FileFullPathRecognizer::isFullPath($path)) {
            $this->file = $path;
        } else {
            $this->file = FilePathCombiner::combine(
                $this->getRootPath(), $path
            );
        }
        $this->pushLayout();
        try {
            $loadFileFunction = $this->loadFileFunction;
            $loadFileFunction();
            $this->file = null;
            if ($this->layoutPath !== null) {
                $this->render($this->layoutPath);
            }
        } finally {
            $this->popLayout();
        }
    }

    /**
     * @param string $path
     * @return void
     */
    public function setLayout($path) {
        $this->layoutPath = $path;
    }

    /**
     * @return string
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * @param string $name
     * @param Closure $default
     * @return void
     */
    public function renderBlock($name, $default = null) {
        if (isset($this->blocks[$name])) {
            $block = $this->blocks[$name];
            $block();
        } else {
            if ($default === null) {
                throw new ViewException("Block '$name' does not exist.");
            }
            $default();
        }
    }

    /**
     * @param string $name
     * @param Closure $value
     * @return void
     */
    public function setBlock($name, $value) {
        $this->blocks[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasBlock($name) {
        return isset($this->blocks[$name]);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        if ($offset === null) {
            throw new InvalidArgumentException(
                "Argument 'offset' cannot be null."
            );
        } else {
            $this->viewModel[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->viewModel[$offset]);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->viewModel[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        if (isset($this->viewModel[$offset]) === false) {
            if (array_key_exists($offset, $this->viewModel)) {
                return;
            }
            throw new ViewException(
                "View model field '$offset' is not defined."
            );
        }
        return $this->viewModel[$offset];
    }

    /**
     * @return array
     */
    public function getViewModel() {
        return $this->viewModel;
    }

    /**
     * @return string
     */
    private function getRootPath() {
        if ($this->rootPath === null) {
            $path = Config::getString(
                'hyperframework.web.view.root_path', 'views'
            );
            $this->rootPath = FileFullPathBuilder::build($path);
        }
        return $this->rootPath;
    }

    /**
     * @return void
     */
    private function pushLayout() {
        array_push($this->layoutPathStack, $this->layoutPath);
        $this->setLayout(null);
    }

    /**
     * @return void
     */
    private function popLayout() {
        $this->layoutPath = array_pop($this->layoutPathStack);
    }
}
