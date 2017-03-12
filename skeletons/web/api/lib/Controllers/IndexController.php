<?php
namespace Controllers;

class IndexController extends Controller {
    public function onShowAction() {
        return ['message' => 'hello world!'];
    }
}
