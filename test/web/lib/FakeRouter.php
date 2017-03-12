<?php
namespace Hyperframework\Web\Test;

class FakeRouter {
    private $params = [];
    private $module;
    private $moduleNamespace;
    private $controller;
    private $controllerClass;
    private $action;
    private $actionMethod;

    public function execute() {
    }

    public function getAction() {
        return $this->action;
    }
 
    public function setAction($action) {
        $this->action = $action;
    }

    public function getActionMethod() {
        return $this->actionMethod;
    }
 
    public function setActionMethod($actionMethod) {
        $this->actionMethod = $actionMethod;
    }

    public function getController() {
        return $this->controller;
    }

    public function setController($controller) {
        $this->controller = $controller;
    }

    public function getControllerClass() {
        return $this->controllerClass;
    }

    public function setControllerClass($controllerClass) {
        $this->controllerClass = $controllerClass;
    }

    public function getModule() {
        return $this->module;
    }

    public function setModule($module) {
        $this->module = $module;
    }

    public function getParam($name) {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
    }

    public function getParams() {
        return $this->params;
    }

    public function setParam($name, $value) {
        $this->params[$name] = $value;
    }

    public function removeParam($name) {
        unset($this->params[$name]);
    }

    public function hasParam($name) {
        return isset($this->params[$name]);
    }
}
