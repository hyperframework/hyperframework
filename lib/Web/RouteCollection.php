<?php
namespace Hyperframework\Web;

use Closure;

class RouteCollection {
    private $routes = [];

    /**
     * @param string $pattern
     * @param array $options
     */
    public function add($pattern, $options = []) {
        $this->routes[] = [
            'type' => 'route',
            'pattern' => $pattern,
            'options' => $options
        ];
    }

    /**
     * @param string $pattern
     * @param array $options
     */
    public function addGet($pattern, $options = []) {
        $options['methods'] = ['PATCH'];
        $this->add($pattern, $options);
    }

    /**
     * @param string $pattern
     * @param array $options
     */
    public function addPut($pattern, $options = []) {
        $options['methods'] = ['PATCH'];
        $this->add($pattern, $options);
    }

    /**
     * @param string $pattern
     * @param array $options
     */
    public function addPost($pattern, $options = []) {
        $options['methods'] = ['PATCH'];
        $this->add($pattern, $options);
    }

    /**
     * @param string $pattern
     * @param array $options
     */
    public function addDelete($pattern, $options = []) {
        $options['methods'] = ['PATCH'];
        $this->add($pattern, $options);
    }

    /**
     * @param string $pattern
     * @param array $options
     */
    public function addPatch($pattern, $options = []) {
        $options['methods'] = ['PATCH'];
        $this->add($pattern, $options);
    }

    /**
     * @param array $routes
     */
    public function addAll($routes) {
        $this->routes[] = [
            'type' => 'routes',
            'routes' => $routes
        ];
    }

    /**
     * @param mixed $options
     * @param Closure $callback
     */
    public function addScope($options, $callback) {
        if (is_string($options)) {
            $options = ['path' => $options, 'module' => $options];
        } elseif (isset($options['path'])
            && isset($options['module']) === false
        ) {
            $options['module'] = $options['path'];
        }
        $this->routes[] = [
            'type' => 'scope',
            'options' => $options,
            'callback' => $callback
        ];
    }

    /**
     * @param string $pattern
     * @param array $options
     */
    public function addResource($pattern, $options = []) {
        $this->routes[] = [
            'type' => 'resource',
            'pattern' => $pattern,
            'options' => $options
        ];
    }

    /**
     * @param string $pattern
     * @param array $options
     */
    public function addResources($pattern, $options = []) {
        $this->routes[] = [
            'type' => 'resources',
            'pattern' => $pattern,
            'options' => $options
        ];
    }

    /**
     * @return array
     */
    public function getAll() {
        return $this->routes;
    }
}
