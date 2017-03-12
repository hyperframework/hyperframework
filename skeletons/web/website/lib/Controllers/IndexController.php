<?php
namespace Controllers;

use Hyperframework\Web\Controller;

class IndexController extends Controller {
    public function onShowAction() {
        return ['message' => 'hello world!'];
    }
}
