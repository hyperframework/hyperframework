<?php
namespace Hyperframework\Web;

use Closure;
use Hyperframework\Common\Config;
use Hyperframework\Common\NamespaceCombiner;
use Hyperframework\Common\Inflector;

abstract class Router {
    private $params = [];
    private $module;
    private $controller;
    private $controllerClass;
    private $action;
    private $actionMethod;
    private $path;
    private $domain;
    private $matchStatus = 'NOT_MATCHED';
    private $allowedMethods = [];

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null) {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        } else {
            return $default;
        }
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasParam($name) {
        return isset($this->params[$name]);
    }

    /**
     * @return string
     */
    public function getModule() {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getControllerClass() {
        if ($this->controllerClass !== null) {
            return $this->controllerClass;
        }
        $controller = (string)$this->getController();
        $tmp = ucwords(str_replace(['_', '-'], ' ', $controller));
        $class = str_replace(' ', '', $tmp) . 'Controller';
        $moduleNamespace = (string)$this->getModuleNamespace();
        $class = NamespaceCombiner::combine($moduleNamespace, $class);
        return $class;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getActionMethod() {
        if ($this->actionMethod !== null) {
            return $this->actionMethod;
        }
        $action = (string)$this->getAction();
        if ($action === '') {
            return;
        }
        $tmp = str_replace(
            ' ', '', ucwords(str_replace(['_', '-'], ' ', $action))
        );
        return 'on' . $tmp . 'Action';
    }

    /**
     * @param RouteCollection $routes
     * @return void
     */
    public function execute($routes = null) {
        if ($routes === null) {
            $routes = $this->buildRoutes();
        }
        if ($this->matchRoutes($routes) === false) {
            $this->handleMatchFailed();
        }
    }

    /**
     * @return RouteCollection
     */
    public function buildRoutes() {
        $routes = new RouteCollection;
        $this->prepare($routes);
        return $routes;
    }

    /**
     * @param RouteCollection $routes
     * @return void
     */
    abstract protected function prepare($routes);

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function setParam($name, $value) {
        $this->params[$name] = $value;
    }

    /**
     * @param string $name
     * @return void
     */
    protected function removeParam($name) {
        unset($this->params[$name]);
    }

    /**
     * @param string $module
     * @return void
     */
    protected function setModule($module) {
        $this->module = (string)$module;
    }

    /**
     * @param string $controller
     * @return void
     */
    protected function setController($controller) {
        $this->controller = (string)$controller;
    }

    /**
     * @param string $controllerClass
     * @return void
     */
    protected function setControllerClass($controllerClass) {
        $this->controllerClass = (string)$controllerClass;
    }

    /**
     * @param string $actionMethod
     * @return void
     */
    protected function setAction($action) {
        $this->action = (string)$action;
    }

    /**
     * @param string $actionMethod
     * @return void
     */
    protected function setActionMethod($actionMethod) {
        $this->actionMethod = (string)$actionMethod;
    }

    /**
     * @return string
     */
    protected function getPath() {
        if ($this->path === null) {
            $this->path = Request::getPath();
        }
        return $this->path;
    }

    /**
     * @return string
     */
    protected function getDomain() {
        if ($this->domain === null) {
            $this->domain = Request::getDomain();
        }
        return $this->domain;
    }

    /**
     * @param string $location
     * @param int $statusCode
     * @return void
     */
    private function redirect($location, $statusCode = 301) {
        $this->setParam('location', $location);
        $this->setParam('status_code', $statusCode);
        $this->setControllerClass(RedirectionController::class);
    }

    /**
     * @param string[] $allowedMethods
     * @return void
     */
    private function setAllowedMethods($allowedMethods) {
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @param string[] $allowedMethods
     * @return void
     */
    private function addAllowedMethods($allowedMethods) {
        foreach ($allowedMethods as $allowedMethod) {
            if (in_array($allowedMethod, $this->allowedMethods) === false) {
                $this->allowedMethods[] = $allowedMethod;
            }
        }
    }

    /**
     * @return string[]
     */
    private function getAllowedMethods() {
        return $this->allowedMethods;
    }

    /**
     * @param string $matchStatus
     * @return void
     */
    private function setMatchStatus($matchStatus) {
        $this->matchStatus = $matchStatus;
    }

    /**
     * @return string
     */
    private function getMatchStatus() {
        return $this->matchStatus;
    }

    /**
     * @return bool
     */
    private function isMatched() {
        return $this->getMatchStatus() === 'MATCHED';
    }

    /**
     * @return void
     */
    private function handleMatchFailed() {
        $matchStatus = $this->getMatchStatus();
        if ($matchStatus === 'NOT_MATCHED') {
            throw new NotFoundException;
        } elseif ($matchStatus === 'METHOD_NOT_MATCHED') {
            throw new MethodNotAllowedException($this->getAllowedMethods());
        } else {
            throw new RoutingException('The match status is invalid.');
        }
    }

    /**
     * @param RouteCollection $routes
     * @param string $module
     * @return bool
     */
    private function matchRoutes($routes, $module = null) {
        foreach ($routes->getAll() as $route) {
            if ($route['type'] === 'route') {
                if ($this->matchRoute($route, $module)) {
                    return true;
                }
            } elseif ($route['type'] === 'routes') {
                foreach ($route['routes'] as $key => $value) {
                    if (is_int($key)) {
                        if ($this->matchRoute([
                            'pattern' => $value, 'options' => []
                        ], $module)) {
                            return true;
                        }
                    } else {
                        if (is_string($value)) {
                            if ($this->matchRoute([
                                'pattern' => $key,
                                'options' => ['to' => $value]
                            ], $module)) {
                                return true;
                            }
                        } else {
                            if ($this->matchRoute([
                                'pattern' => $key, 'options' => $value
                            ], $module)) {
                                return true;
                            }
                        }
                    }
                }
            } elseif ($route['type'] === 'scope') {
                if ($this->matchScope(
                    $route['options'], $route['callback'], $module
                )) {
                    if (isset($route['options']['success'])) {
                        $route['options']['success']();
                    }
                    return true;
                }
            } elseif ($route['type'] === 'resource') {
                if ($this->matchResource(
                    $route['pattern'], $route['options'], $module
                )) {
                    if (isset($route['options']['success'])) {
                        $route['options']['success']();
                    }
                    return true;
                }
            } elseif ($route['type'] === 'resources') {
                if ($this->matchResource(
                    $route['pattern'], $route['options'], $module
                )) {
                    if (isset($route['options']['success'])) {
                        $route['options']['success']();
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param array $route
     * @param string $module
     * @return bool
     */
    private function matchRoute($route, $module) {
        if ($this->match($route['pattern'], $route['options'])) {
            if (isset($route['options']['redirect_to'])) {
                $this->parseRedirectionResult($route['options']['redirect_to']);
            } elseif (isset($route['options']['to'])) {
                $this->parseResult($module, $route['options']['to']);
            } else {
                $this->parseResult($module, $route['pattern']);
            }
            if (isset($route['options']['success'])) {
                $route['options']['success']();
            }
            return true;
        }
        return false;
    }

    /**
     * @param string $pattern
     * @param array $options
     * @return bool
     */
    private function match($pattern, $options) {
        $hasBackslash = strpos($pattern, '\\') !== false;
        $hasOptionalSegment = strpos($pattern, '(') !== false;
        $hasDynamicSegment = strpos($pattern, ':') !== false;
        $hasWildcardSegment = strpos($pattern, '*') !== false;
        $hasFormat = false;
        $formats = null;
        if (isset($options['formats'])) {
            $hasFormat = true;
            $formats = $options['formats'];
        } elseif(isset($options['format'])) {
            $hasFormat = true;
            $formats = [$options['format']];
        } elseif(isset($options['has_format'])) {
            $hasFormat = true;
        }
        $path = trim($this->getPath(), '/');
        if ($hasFormat === false
            && $hasOptionalSegment === false
            && $hasWildcardSegment === false
            && $hasDynamicSegment === false
        ) {
            if ($path === trim($pattern, '/')) {
                if (isset($options['extra'])) {
                    $isMatched =
                        $this->checkExtraRules($options['extra']);
                    if ($isMatched === false) {
                        return false;
                    }
                }
                if ($this->checkMethod($options)) {
                    $this->setMatchStatus('MATCHED');
                    return true;
                }
            }
            return false;
        }
        if (strpos($pattern, '#') !== false) {
            throw new RoutingException(
                "Invalid pattern '$pattern', the character '#' is not allowed."
            );
        }
        if (strpos($pattern, '?') !== false) {
            throw new RoutingException(
                "Invalid pattern '$pattern', the character '?' is not allowed."
            );
        }
        $originalPattern = $pattern;
        $pattern = trim($pattern, '/');
        if ($hasBackslash) {
            $hasBackslash = true;
            $pattern = str_replace(
                ['\:', '\*', '\(', '\)'], ['#0', '#1', '#2', '#3'], $pattern
            );
            $backslashPosition = strpos($pattern, '\\');
            if ($backslashPosition !== false) {
                if ($backslashPosition === strlen($originalPattern) - 1) {
                    $message = "Invalid pattern '$originalPattern', '\\'"
                        . " at the end of the pattern is not allowed.";
                } else {
                    $message = "Invalid pattern '$originalPattern', '\\'"
                        . " is not allowed before '"
                        . $originalPattern[$backslashPosition + 1] . "'.";
                }
                throw new RoutingException($message);
            }
        }
        $pattern = str_replace(
            ['.', '^', '$', '+', '[', '|', '{', '*'],
            ['\.', '\^', '\$', '\+', '\[', '\|', '\{', '\*'],
            $pattern
        );
        if ($hasOptionalSegment) {
            $length = strlen($pattern);
            $count = 0;
            for ($index = 0; $index < $length; ++$index) {
                if ($pattern[$index] === '(') {
                    ++$count;
                }
                if ($pattern[$index] === ')') {
                    --$count;
                    if ($count < 0) {
                        break;
                    }
                }
            }
            if ($count !== 0) {
                $source = '(';
                if ($count < 0) {
                    $source = ')';
                }
                throw new RoutingException("Invalid pattern '$originalPattern',"
                    . " '$source' is not closed.");
            }
            $pattern = str_replace(')', ')?', $pattern);
        }
        $namedSegments = [];
        if ($hasFormat) {
            $namedSegments[] = 'format';
        }
        $namedSegmentPattern = '[^/]+';
        $duplicatedNamedSegment = null;
        $callback = function($matches) use (
            &$namedSegments,
            &$duplicatedNamedSegment,
            &$namedSegmentPattern
        ) {
            $segment = $matches[1];
            if (isset($namedSegments[$segment])
                && $duplicatedNamedSegment === null
            ) {
                $duplicatedNamedSegment = $segment;
            } else {
                $namedSegments[$segment] = true;
            }
            return "(?<$segment>$namedSegmentPattern?)";
        };
        if ($hasDynamicSegment) {
            $pattern = preg_replace_callback(
                '#\\\\\{:([a-zA-Z_][a-zA-Z0-9_]*)}#', $callback, $pattern
            );
            $pattern = preg_replace_callback(
                '#:([a-zA-Z_][a-zA-Z0-9_]*)#', $callback, $pattern
            );
        }
        if ($hasWildcardSegment) {
            $namedSegmentPattern = '.+';
            $pattern = preg_replace_callback(
                '#\\\\\{\\\\\*([a-zA-Z_][a-zA-Z0-9_]*)}#', $callback, $pattern
            );
            $pattern = preg_replace_callback(
                '#\\\\\*([a-zA-Z_][a-zA-Z0-9_]*)#', $callback, $pattern
            );
        }
        if ($duplicatedNamedSegment !== null) {
            throw new RoutingException(
                "Invalid pattern '$originalPattern', "
                    . "named segment '$duplicatedNamedSegment' is duplicated."
            );
        }
        $formatPattern = null;
        $isOptionalFormat = isset($options['default_format']);
        if ($hasFormat) {
            if ($isOptionalFormat) {
                $formatPattern = '(\.(?<format>[0-9a-zA-Z]+?))?';
            } else {
                $formatPattern = '\.(?<format>[0-9a-zA-Z]+?)';
            }
        }
        if ($hasBackslash) {
            $pattern = str_replace(
                ['#0', '#1', '#2', '#3'], ['\:', '\*', '\(', '\)'], $pattern
            );
        }
        $pattern = '#^' . $pattern . $formatPattern . '$#';
        $result = preg_match($pattern, $path, $matches);
        if ($result === 1) {
            foreach ($options as $key => $value) {
                if (is_string($key) && $key !== '' && $key[0] === ':') {
                    if ($hasFormat && $key === ':format') {
                        throw new RoutingException(
                            "Dynamic segment ':format' is reserved, use "
                                . "option 'format' to change"
                                . " the rule of format."
                        );
                    }
                    $name = substr($key, 1);
                    if (isset($matches[$name]) === false) {
                        continue;
                    }
                    $segment = $matches[$name];
                    if (strpos($value, '#') !== false) {
                        throw new RoutingException(
                            "Invalid pattern '$value', character '#' is not"
                                . " allowed, defined in option '$key'."
                        );
                    }
                    $result = preg_match('#^' . $value . '$#', $segment);
                    if ($result === false) {
                        throw new RoutingException(
                            "Invalid pattern '$value', defined in option '"
                                . "$key'."
                        );
                    }
                    if ($result !== 1) {
                        return false;
                    }
                }
            }
            if ($hasFormat) {
                if (isset($matches['format']) === false) {
                    if (isset($options['default_format'])) {
                        $this->setParam(
                            'format', $options['default_format']
                        );
                    } else {
                        return false;
                    }
                } elseif ($formats !== null
                    && in_array($matches['format'], $formats) === false
                ) {
                    return false;
                }
            }
            if (isset($options['extra'])) {
                $extraRules = $options['extra'];
                if ($this->checkExtraRules($extraRules, $matches) === false) {
                    return false;
                }
            }
            if ($this->checkMethod($options)) {
                $this->setMatches($matches);
                $this->setMatchStatus('MATCHED');
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $options
     * @param Closure $callback
     * @param string $module
     * @return bool
     */
    private function matchScope($options, $callback, $module) {
        if (isset($options['path'])) {
            $path = trim($options['path'], '/');
            $orignalPath = $this->getPath();
            $trimmedPath = trim($orignalPath, '/');
            $currentPath = '/';
            if ($path !== '') {
                $pathLength = strlen($path);
                if (strncmp($path, $trimmedPath, $pathLength) === 0) {
                    $previousPathLength = strlen($trimmedPath);
                    if ($previousPathLength !== $pathLength) {
                        if ($trimmedPath[$pathLength] !== '/') {
                            return false;
                        }
                        $currentPath = substr($trimmedPath, $pathLength);
                    }
                } else {
                    return false;
                }
            }
            $this->setPath($currentPath);
        }
        if (isset($options['domain'])) {
            $originalDomain = $this->getDomain();
            $currentDomain = $originalDomain;
            $domain = (string)$options['domain'];
            if ($domain !== '') {
                $domainLength = strlen($domain);
                if (substr($originalDomain, -$domainLength) === $domain) {
                    if (strlen($originalDomain) > $domainLength) {
                        if ($originalDomain[$domainLength] !== '.') {
                            return false;
                        }
                        $currentDomain = substr(
                            $originalDomain, 0, -($domainLength + 1)
                        );
                    } else {
                        $currentDomain = null;
                    }
                } else {
                    return false;
                }
            }
            $this->setDomain($currentDomain);
        }
        $routes = new RouteCollection;
        if (isset($options['module'])) {
            $submodule = (string)$options['module'];
            if ($submodule !== '' && $submodule !== '/') {
                if ($module !== null) {
                    $module .= '.' . $submodule;
                } else {
                    $module = $submodule;
                }
            }
        }
        $callback($routes);
        $this->matchRoutes($routes, $module);
        if (isset($options['path'])) {
            $this->setPath($orignalPath);
        }
        if (isset($options['domain'])) {
            $this->setDomain($originalDomain);
        }
        return $this->isMatched();
    }

    /**
     * @param string $pattern
     * @param array $options
     * @param string $module
     * @return bool
     */
    private function matchResource($pattern, $options = [], $module) {
        $actionOptions = ['actions', 'default_actions'];
        foreach ($actionOptions as $actionOption) {
            if (isset($options[$actionOption])
                && is_array($options[$actionOption]) === false
            ) {
                throw new RoutingException(
                    "Option '$actionOption' must be an array, "
                        . gettype($options[$actionOption]) . ' given.'
                );
            }
        }
        $defaultActions = null;
        if (isset($options['default_actions'])) {
            $defaultActions = $options['default_actions'];
            unset($options['default_actions']);
        } else {
            $defaultActions = [
                'show'   => ['GET', '/'],
                'new',
                'update' => [['PATCH', 'PUT'], '/'],
                'create' => ['POST', '/'],
                'delete' => ['DELETE', '/'],
                'edit'
            ];
        }
        if (isset($options['actions'])) {
            $actions = $options['actions'];
            if ($options['actions'] !== false) {
                foreach ($actions as $key => $value) {
                    if (is_int($key)) {
                        if (is_string($value) === false) {
                            throw new RoutingException(
                                'The action name must be a string, '
                                    . gettype($value) . ' given.'
                            );
                        }
                        if (isset($defaultActions[$value])) {
                            $actions[$value] = $defaultActions[$value];
                        } else {
                            $actions[$value] = [];
                        }
                    }
                }
            } else {
                $actions = [];
            }
            unset($options['actions']);
        } else {
            $actions = $defaultActions;
            foreach ($actions as $key => $value) {
                if (is_int($key)) {
                    unset($actions[$key]);
                    if (is_string($value) === false) {
                        throw new RoutingException(
                            'The action name must be a string, '
                                . gettype($value) . ' given.'
                        );
                    }
                    if (isset($defaultActions[$value])) {
                        $actions[$value] = $defaultActions[$value];
                    } else {
                        $actions[$value] = [];
                    }
                }
            }
        }
        if (count($actions) === 0) {
            return false;
        }
        $pattern = rtrim($pattern, '/');
        $action = null;
        foreach ($actions as $action => $value) {
            if (is_array($value) === false) {
                $value = [$value];
            }
            if (isset($value[0])) {
                if (is_string($value[0])) {
                    $value['methods'] = [$value[0]];
                } elseif (is_array($value[0]) === false) {
                    throw new RoutingException(
                        "Allowed request methods of action '$action'"
                            . " must be a string or an array, "
                            . gettype($value[0]) . ' given.'
                    );
                } else {
                    $value['methods'] = $value[0];
                }
            } else {
                $value['methods'] = ['GET'];
            }
            unset($value[0]);
            if (isset($value[1])) {
                if (is_string($value[1]) === false) {
                    throw new RoutingException(
                        "The path of action '$action' must be a string, "
                            . gettype($value[1]) . ' given.'
                    );
                }
                $suffix = $value[1];
                unset($value[1]);
            } else {
                $suffix = $action;
            }
            if (count($value) !== 0) {
                $actionOptions = $value;
                $actionExtra = null;
                if (isset($actionOptions['extra'])) {
                    $actionExtra = $actionOptions['extra'];
                }
                $actionOptions = $actionOptions + $options;
                if (isset($options['extra']) && $actionExtra !== null) {
                    $extra = $options['extra'];
                    if (is_array($extra) === false) {
                        $extra = [$extra];
                    }
                    if (is_array($actionExtra)) {
                        $extra = array_merge($extra, $actionExtra);
                    } else {
                        $extra[] = $actionExtra;
                    }
                    $actionOptions['extra'] = $extra;
                }
            } else {
                $actionOptions = $options;
            }
            $actionPattern = $pattern;
            $suffix = trim($suffix, '/');
            if ($suffix !== '') {
                $actionPattern .= '/' . $suffix;
            }
            if ($this->match($actionPattern, $actionOptions)) {
                break;
            }
        }
        if ($this->isMatched()) {
            if (isset($options['module'])) {
                if ($module !== null) {
                    $module = $module . '.' . $options['module'];
                } else {
                    $module = $options['module'];
                }
            }
            if ($module !== null) {
                $this->setModule($module);
            }
            if (isset($options['controller'])) {
                $this->setController($options['controller']);
            } else {
                $controller = $pattern;
                if (($slashPosition = strrpos($pattern, '/')) !== false) {
                    $controller = substr($pattern, $slashPosition + 1);
                }
                $this->setController($controller);
            }
            $this->setAction($action);
            return true;
        }
        return false;
    }

    /**
     * @param string $pattern
     * @param array $options
     * @param string $module
     * @return bool
     */
    private function matchResources($pattern, $options = [], $module) {
        if (preg_match('#[:*]id($|[/{])#', $pattern) !== 0) {
            throw new RoutingException(
                "Invalid pattern '$pattern', "
                    . "dynamic segment ':id' is reserved."
            );
        }
        if (isset($options[':id'])) {
            throw new RoutingException(
                "Invalid option ':id', "
                    . "use option 'id' to change the pattern of element id."
            );
        }
        if (isset($options['id'])) {
            $options[':id'] = $options['id'];
        } else {
            $options[':id'] = '\d+';
        }
        $actionOptions = [
            'default_actions', 'element_acitons', 'collection_actions'
        ];
        foreach ($actionOptions as $actionOption) {
            if (isset($options[$actionOption])
                && is_array($options[$actionOption]) === false
            ) {
                throw new RoutingException(
                    "Option '$actionOption' must be an array, "
                        . gettype($options[$actionOption]) . ' given.'
                );
            }
        }
        if (isset($options['default_actions']) === false) {
            $defaultActions = [
                'index'  => ['GET', '/', 'belongs_to' => 'collection'],
                'new'    => ['belongs_to' => 'collection'],
                'create' => ['POST', '/', 'belongs_to' => 'collection'],
                'show'   => ['GET', '/', 'belongs_to' => 'element'],
                'edit'   => ['belongs_to' => 'element'],
                'update' => [
                    ['PATCH', 'PUT'], '/', 'belongs_to' => 'element'
                ],
                'delete' => ['DELETE', '/', 'belongs_to' => 'element'],
            ];
        } else {
            $defaultActions = $options['default_actions'];
            foreach ($defaultActions as $key => $value) {
                if (isset($value['belongs_to']) === false) {
                    throw new RoutingException(
                        "Default action '$key' is invalid, "
                            . "field 'belongs_to' is missing."
                    );
                }
                if ($value['belongs_to'] !== 'collection'
                    && $value['belongs_to'] !== 'element'
                ) {
                    throw new RoutingException(
                        "Default action '$key' is invalid, "
                            . "the value of field 'belongs_to'"
                            . " must be equal to 'collection' or 'element'."
                    );
                }
            }
        }
        if (isset($options['collection_actions'])) {
            foreach ($options['collection_actions'] as $key => $value) {
                if (is_int($key)) {
                    if (isset($defaultActions[$value])) {
                        $action = $defaultActions[$value];
                        if ($action['belongs_to'] === 'element') {
                            unset($options['collection_actions'][$key]);
                            $options['collection_actions'][$value] = [];
                        }
                    }
                }
            }
            $options['actions'] = $options['collection_actions'];
        } else {
            $options['actions'] = [];
            foreach ($defaultActions as $key => $value) {
                if ($value['belongs_to'] === 'collection') {
                    $actionName = $value;
                    if (is_int($key)) {
                        $options['actions'][] = $value;
                    } else {
                        $options['actions'][] = $key;
                    }
                }
            }
        }
        if (isset($options['element_actions'])) {
            $actions = $this->convertElementActionsToCollectionActions(
                $options['element_actions'], $defaultActions
            );
            $options['actions'] = array_merge(
                $options['actions'], $actions
            );
        } else {
            foreach ($defaultActions as $key => $value) {
                if ($value['belongs_to'] === 'element') {
                    if (is_int($key)) {
                        $options['actions'][] = $value;
                    } else {
                        $options['actions'][] = $key;
                    }
                }
            }
        }
        $options['default_actions'] =
            $this->convertElementActionsToCollectionActions(
                $defaultActions, null, true
            );
        $isMatched = $this->matchResource($pattern, $options, $module);
        if ($isMatched) {
            if (isset($options['controller']) === false) {
                $controller = (string)$this->getController();
                if ($controller === '') {
                    return true;
                }
                $hyphenPosition = strrpos($controller, '-');
                $underscorePosiiton = strrpos($controller, '_');
                $separatorPosition = $hyphenPosition;
                if ($hyphenPosition < $underscorePosiiton) {
                    $separatorPosition = $underscorePosiiton;
                }
                $controllerPrefix = '';
                $controllerLastWord = $controller;
                if ($separatorPosition > 0) {
                    $controllerPrefix = substr(
                        $controller, 0, $separatorPosition + 1
                    );
                    $controllerLastWord = substr(
                        $controller, $separatorPosition + 1
                    );
                    if ($controllerLastWord === '') {
                        return true;
                    }
                }
                $inflectorClass = Config::getClass(
                    'hyperframework.inflector_class', Inflector::class
                );
                $controllerLastWord = $inflectorClass::singularize(
                    $controllerLastWord
                );
                $this->setController($controllerPrefix . $controllerLastWord);
            }
        }
        return $isMatched;
    }

    /**
     * @return string
     */
    private function getModuleNamespace() {
        $rootNamespace = 'Controllers';
        $appRootNamespace = Config::getAppRootNamespace();
        $rootNamespace = NamespaceCombiner::combine(
            $appRootNamespace, $rootNamespace
        );
        $module = (string)$this->getModule();
        if ($module === '') {
            return $rootNamespace;
        }
        $tmp = str_replace(
            ' ', '\\', ucwords(str_replace('/', ' ', $module))
        );
        $namespace = str_replace(
            ' ', '', ucwords(str_replace(['_', '-'], ' ', $tmp))
        );
        return NamespaceCombiner::combine($rootNamespace, $namespace);
    }

    /**
     * @param array $actions
     * @param array $defaultActions
     * @param bool $isMixed
     * @return array
     */
    private function convertElementActionsToCollectionActions(
        $actions, $defaultActions = null, $isMixed = false
    ) {
        $result = [];
        foreach ($actions as $key => $value) {
            if (is_int($key)) {
                if (isset($defaultActions[$value])
                    && $defaultActions[$value]['belongs_to'] === 'element'
                ) {
                    $key = $value;
                    $value = $defaultActions[$value];
                    if ($isMixed === false) {
                        unset($value['belongs_to']);
                    }
                } else {
                    if ($isMixed) {
                        $result[$key] = $value;
                        continue;
                    }
                    if (is_string($value) === false) {
                        throw new RoutingException(
                            'The action name must be a string, '
                                . gettype($value) . ' given.'
                        );
                    }
                    $key = $value;
                    $value = ['GET', ':id/' . ltrim($value, '/')];
                    $result[$key] = $value;
                    continue;
                }
            }
            if ($isMixed) {
                if ($value['belongs_to'] === 'collection') {
                    $result[$key] = $value;
                    continue;
                } else {
                    unset($value['belongs_to']);
                }
            }
            if (is_array($value)) {
                if (isset($value[1])) {
                    if (is_string($value[1]) === false) {
                        throw new RoutingException(
                            "The path of action '$key' must be a string, "
                                . gettype($value[1]) . ' given.'
                        );
                    }
                    $path = $value[1];
                } else {
                    if (isset($value[0]) === false) {
                        $value[0] = 'GET';
                    }
                    $path = $key;
                }
                $path = ltrim($path, '/');
                if ($path !== '') {
                    $value[1] = ':id/' . $path;
                } else {
                    $value[1] = ':id';
                }
            } else {
                $value = [$value, ':id'];
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * @param array $options
     * @return bool
     */
    private function checkMethod($options) {
        if (isset($options['methods'])) {
            if (is_array($options['methods']) === false) {
                throw new RoutingException(
                    "Option 'methods' must be an array, "
                        . gettype($options['methods']) . " given."
                );
            }
            $isMethodAllowed = false;
            $requestMethod = Request::getMethod();
            foreach ($options['methods'] as $method) {
                if (strtoupper($method) === $requestMethod) {
                    $isMethodAllowed = true;
                    break;
                }
            }
            if ($isMethodAllowed === false) {
                $this->setMatchStatus('METHOD_NOT_MATCHED');
                $this->addAllowedMethods($options['methods']);
                return false;
            }
        }
        return true;
    }

    /**
     * @param mixed $extra
     * @param array $matches
     * @return bool
     */
    private function checkExtraRules($extra, $matches = []) {
        foreach ($matches as $key => $value) {
            if (is_int($key)) {
                unset($matches[$key]);
            }
        }
        if (is_array($extra)) {
            foreach ($extra as $function) {
                if ($function instanceof Closure === false) {
                    $type = gettype($function);
                    if ($type === 'Object') {
                        $type = get_class($function);
                    }
                    throw new RoutingException(
                        'The extra rule must be a closure, ' . $type . ' given.'
                    );
                }
                $result = (bool)$function($matches);
                if ($result !== true) {
                    return false;
                }
            }
            return true;
        } else {
            if ($extra instanceof Closure) {
                return (bool)$extra($matches);
            }
            $type = gettype($extra);
            if ($type === 'Object') {
                $type = get_class($extra);
            }
            throw new RoutingException(
                'The extra rule must be a closure, ' . $type . ' given.'
            );
        }
    }

    /**
     * @param array $matches
     * @return void
     */
    private function setMatches($matches) {
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $this->setParam($key, $value);
            }
        }
    }

    /**
     * @param string $module
     * @param string $result
     * @return void
     */
    private function parseResult($module, $result) {
        if ($result instanceof Closure) {
            $result = $result();
        }
        $hasController = true;
        if (strpos($result, '/') === false) {
            $result = '/' . $result;
            $hasController = false;
        }
        if ($module !== null) {
            $result = $module . '/' . $result;
        }
        $segments = explode('/', $result);
        if (count($segments) === 2) {
            $controller = $segments[0];
            $action = $segments[1];
        } else {
            $action = array_pop($segments);
            $controller = array_pop($segments);
            $this->setModule(implode('/', $segments));
        }
        if ($hasController) {
            if ($controller === '') {
                $controller = null;
            }
            $this->setController($controller);
        }
        if ($action === '') {
            throw new RoutingException(
                'The result is invalid, the action cannot be empty.'
            );
        }
        $this->setAction($action);
    }

    /**
     * @param mixed $redirectionResult
     * @return void
     */
    private function parseRedirectionResult($redirectionResult) {
        if ($redirectionResult instanceof Closure) {
            $redirectionResult = $redirectionResult();
        }
        if (is_string($redirectionResult)) {
            $redirectionResult = ['location' => $redirectionResult];
        }
        if (is_array($redirectionResult)) {
            if (isset($redirectionResult['location']) === false) {
                throw new RoutingException(
                    'The redirection result is invalid, '
                        . "field 'location' is missing."
                );
            }
            $params = [$redirectionResult['location']];
            if (isset($redirectionResult['status_code'])) {
                $params[] = $redirectionResult['status_code'];
            }
            call_user_func_array([$this, 'redirect'], $params);
        } else {
            throw new RoutingException(
                'The redirection result is invalid.'
            );
        }
    }

    /**
     * @param string $path
     * @return void
     */
    private function setPath($path) {
        $this->path = (string)$path;
    }

    /**
     * @param string $domain
     * @return void
     */
    private function setDomain($domain) {
        $this->domain = $domain;
    }
}
