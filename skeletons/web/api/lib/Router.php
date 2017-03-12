<?php
use Hyperframework\Web\Router as Base;

class Router extends Base {
    protected function prepare($routes) {
        $routes->addAll([
            '/' => 'index/show'
        ]);
    }
}
