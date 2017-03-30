<?php
namespace Hyperframework\Web;

use Generator;
use Closure;
use Exception;
use Throwable;
use UnexpectedValueException;
use Hyperframework\Common\Config;
use Hyperframework\Common\InvalidOperationException;
use Hyperframework\Common\ClassNotFoundException;

abstract class Controller {
    private $app;
    private $filterChain = [];
    private $isFilterChainReversed = false;
    private $isQuitFilterChainMethodCalled = false;
    private $isRunMethodCalled = false;
    private $actionResult;
    private $view;
    private $isViewEnabled = true;

    /**
     * @param App $app
     */
    public function __construct($app) {
        $this->app = $app;
        $this->addBeforeFilter(function() {
            $this->initializeGlobalPostData();
        });
        $this->addBeforeFilter(function() {
            $this->checkCsrf();
        });
    }

    /**
     * @return void
     */
    public function run() {
        if ($this->isRunMethodCalled) {
            throw new InvalidOperationException(
                'The run method of ' . __CLASS__
                    . ' cannot be called more than once.'
            );
        }
        $this->isRunMethodCalled = true;
        $e = null;
        try {
            if ($this->isQuitFilterChainMethodCalled) {
                return;
            }
            $this->runBeforeFilters();
            if ($this->isQuitFilterChainMethodCalled) {
                return;
            }
            $this->handleAction();
            if ($this->isQuitFilterChainMethodCalled) {
                return;
            }
            $this->runAfterFilters();
        } catch (Exception $e) {} catch (Throwable $e) {}
        if ($e !== null) {
            $this->quitFilterChain($e);
            if ($e !== null) {
                throw $e;
            }
        }
    }

    /**
     * @return App
     */
    public function getApp() {
        return $this->app;
    }

    /**
     * @return Router
     */
    public function getRouter() {
        return $this->getApp()->getRouter();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getRouteParam($name, $default = null) {
        return $this->getRouter()->getParam($name, $default);
    }

    /**
     * @return array
     */
    public function getRouteParams() {
        return $this->getRouter()->getParams();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasRouteParam($name) {
        return $this->getRouter()->hasParam($name);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getQueryParam($name, $default = null) {
        return Request::getQueryParam($name, $default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasQueryParam($name) {
        return Request::hasQueryParam($name);
    }

    /**
     * @return array
     */
    public function getQueryParams() {
        return Request::getQueryParams();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getBodyParam($name, $default = null) {
        return Request::getBodyParam($name, $default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasBodyParam($name) {
        return Request::hasBodyParam($name);
    }

    /**
     * @return array
     */
    public function getBodyParams() {
        return Request::getBodyParams();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getCookieParam($name, $default = null) {
        return Request::getCookieParam($name, $default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasCookieParam($name) {
        return Request::hasCookieParam($name);
    }

    /**
     * @return array
     */
    public function getCookieParams() {
        return Request::getCookieParams();
    }

    /**
     * @return string
     */
    public function getOutputFormat() {
        return $this->getRouteParam('format');
    }

    public function disableView() {
        $this->isViewEnabled = false;
    }

    public function enableView() {
        $this->isViewEnabled = true;
    }

    /**
     * @return bool
     */
    public function isViewEnabled() {
        return $this->isViewEnabled;
    }

    /**
     * @param mixed $view
     */
    public function setView($view) {
        $this->view = $view;
    }

    /**
     * @return mixed
     */
    public function getView() {
        if ($this->view !== null) {
            return $this->view;
        }
        $router = $this->getRouter();
        $module = (string)$router->getModule();
        if ($module !== '') {
            $name = $module . '/';
        } else {
            $name = '';
        }
        $controller = (string)$router->getController();
        if ($controller !== '') {
            $name .= $controller . '/';
        }
        $action = (string)$router->getAction();
        if ($action === '') {
            throw new UnexpectedValueException('The action cannot be empty.');
        }
        $name .= $action;
        return ViewPathBuilder::build($name, $this->getOutputFormat());
    }

    /**
     * @return void
     */
    public function renderView() {
        $view = $this->getView();
        if (is_object($view)) {
            $view->render($this->getActionResult());
            return;
        } elseif (is_string($view) === false) {
            throw new UnexpectedValueException(
                "The view must be a string or an object, "
                    . gettype($view) . " given."
            );
        }
        $path = $view;
        if ($path === '') {
            throw new UnexpectedValueException('The view path cannot be empty.');
        }
        $viewModel = $this->getActionResult();
        if ($viewModel !== null && is_array($viewModel) === false) {
            throw new UnexpectedValueException(
                'The view model must be an array, '
                    . gettype($viewModel) . ' given.'
            );
        }
        $view = ViewFactory::createView($viewModel);
        $view->render($path);
    }

    /**
     * @return mixed
     */
    public function getActionResult() {
        return $this->actionResult;
    }

    /**
     * @param mixed
     * @return void
     */
    public function setActionResult($actionResult) {
        $this->actionResult = $actionResult;
    }

    /**
     * @param string $location
     * @param int $statusCode
     * @return void
     */
    public function redirect($location, $statusCode = 302) {
        Response::setHeader('Location: ' . $location, true, $statusCode);
        $this->disableView();
        $this->quitFilterChain();
    }

    /**
     * @param string|Closure $filter
     * @param array $options
     * @return void
     */
    public function addBeforeFilter($filter, $options = []) {
        $this->addFilter('before', $filter, $options);
    }

    /**
     * @param string|Closure $filter
     * @param array $options
     * @return void
     */
    public function addAfterFilter($filter, $options = []) {
        $this->addFilter('after', $filter, $options);
    }

    /**
     * @param string|Closure $filter
     * @param array $options
     * @return void
     */
    public function addAroundFilter($filter, $options = []) {
        $this->addFilter('around', $filter, $options);
    }

    /**
     * @return void
     */
    protected function checkCsrf() {
        if (CsrfProtection::isEnabled()) {
            CsrfProtection::run();
        }
    }

    /**
     * @return void
     */
    protected function initializeGlobalPostData() {
        if (Config::getBool(
            'hyperframework.web.initialize_global_post_data', false
        )) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $_POST = $this->getBodyParams();
            }
        }
    }

    /**
     * @return void
     */
    protected function handleAction() {
        $router = $this->getRouter();
        $method = $router->getActionMethod();
        if ($method == '') {
            throw new UnexpectedValueException(
                'The action method cannot be empty.'
            );
        }
        if (method_exists($this, $method)) {
            $actionResult = $this->$method();
            $this->setActionResult($actionResult);
        }
        if ($this->isViewEnabled()) {
            $this->renderView();
        }
    }

    /**
     * @return void
     */
    private function runBeforeFilters() {
        foreach ($this->filterChain as &$config) {
            if ($this->isQuitFilterChainMethodCalled === false) {
                $type = $config['type'];
                if ($type === 'before' || $type === 'around') {
                    $this->runFilter($config);
                }
            } else {
                return;
            }
        }
    }

    /**
     * @return void
     */
    private function runAfterFilters() {
        if ($this->isFilterChainReversed === false) {
            $this->filterChain = array_reverse($this->filterChain);
            $this->isFilterChainReversed = true;
        }
        foreach ($this->filterChain as &$config) {
            if ($this->isQuitFilterChainMethodCalled === false) {
                $type = $config['type'];
                if ($type === 'after' || $type === 'yielded') {
                    $this->runFilter($config);
                }
            } else {
                return;
            }
        }
    }

    /**
     * @param array &$config
     * @param bool $shouldReturnResult
     * @return mixed
     */
    private function runFilter(&$config, $shouldReturnResult = false) {
        $result = null;
        if (is_string($config['filter'])) {
            $class = $config['filter'];
            if (class_exists($class) === false) {
                throw new ClassNotFoundException(
                    "Action filter class '$class' does not exist."
                );
            }
            $filter = new $class;
            $result = $filter->run($this);
        } elseif ($config['type'] === 'yielded') {
            $result = $config['filter']->next();
            $config['type'] = 'closed';
        } else {
            $callback = $config['filter'];
            $result = $callback();
        }
        if ($config['type'] === 'around') {
            if ($result instanceof Generator === false) {
                $result = false;
            } else {
                $config['type'] = 'yielded';
                $config['filter'] = $result;
                $result = null;
            }
        }
        if ($shouldReturnResult === false && $result === false) {
            $this->quitFilterChain();
        }
        return $result;
    }

    /**
     * @param string $type
     * @param string|Closure $filter
     * @param array $options
     * @return void
     */
    private function addFilter($type, $filter, $options) {
        if (is_string($filter)) {
            if ($filter === '') {
                throw new ActionFilterException(
                    'The action filter cannot be an empty string.'
                );
            }
        } elseif (is_object($filter) === false
            || $filter instanceof Closure === false
        ) {
            $type = gettype($filter);
            if ($type === 'object') {
                $type = get_class($filter);
            }
            throw new ActionFilterException(
                "The action filter must be a closure or a class name,"
                    . " $type given."
            );
        }
        $config = [
            'type' => $type, 'filter' => $filter, 'options' => $options
        ];
        $action = (string)$this->getRouter()->getAction();
        if ($action === '') {
            throw new UnexpectedValueException('The action cannot be empty.');
        }
        if (isset($options['actions'])) {
            if (is_array($options['actions']) === false) {
                $type = gettype($options['actions']);
                throw new ActionFilterException(
                    "Option 'actions' must be an array, $type given."
                );
            } elseif (in_array($action, $options['actions']) === false) {
                return;
            }
        }
        if (isset($options['ignored_actions'])) {
            if (is_array($options['ignored_actions']) === false) {
                $type = gettype($options['ignored_actions']);
                throw new ActionFilterException(
                    "Option 'ignored_actions' must be an array, $type given."
                );
            } elseif (in_array($action, $options['ignored_actions'])) {
                return;
            }
        }
        if (isset($options['prepend']) && $options['prepend'] === true) {
            array_unshift($this->filterChain, $config);
        } else {
            $this->filterChain[] = $config;
        }
    }

    /**
     * @param Throwable &$exception
     * @return void
     */
    private function quitFilterChain(&$exception = null) {
        if ($this->isQuitFilterChainMethodCalled === false) {
            $this->isQuitFilterChainMethodCalled = true;
            $shouldRunYieldedFiltersOnly = $exception === null
                || $this->isFilterChainReversed === false;
            $shouldRunAfterFilter = false;
            if ($this->isFilterChainReversed === false) {
                $this->filterChain = array_reverse($this->filterChain);
                $this->isFilterChainReversed = true;
            }
            foreach ($this->filterChain as &$filterConfig) {
                if ($filterConfig['type'] === 'yielded' ||
                    ($shouldRunAfterFilter && $filterConfig['type'] === 'after')
                ) {
                    try {
                        if ($exception !== null) {
                            $result =
                                $filterConfig['filter']->throw($exception);
                            $shouldRunAfterFilter = $result !== false
                                && $shouldRunYieldedFiltersOnly === false;
                            $exception = null;
                        } else {
                            $result = $this->runFilter($filterConfig, true);
                            if ($result === false) {
                                $shouldRunYieldedFiltersOnly = true;
                                $shouldRunAfterFilter = false;
                            }
                        }
                    } catch (Exception $exception) {
                        $shouldRunAfterFilter = false;
                    } catch (Throwable $exception) {
                        $shouldRunAfterFilter = false;
                    }
                }
            }
        }
    }
}
