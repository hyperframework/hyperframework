<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;

class ViewFactory {
    /**
     * @param mixed $viewModel
     * @return View
     */
    public static function createView($viewModel = null) {
        $class = Config::getClass('hyperframework.web.view.class', View::class);
        return new $class($viewModel);
    }
}
