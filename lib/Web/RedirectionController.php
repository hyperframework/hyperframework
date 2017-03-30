<?php
namespace Hyperframework\Web;

class RedirectionController {
    private $app;

    /**
     * @param App $app
     */
    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * @return void
     */
    public function run() {
        $router = $app->getRouter();
        Response::setHeader(
            'Location: ' . $router->getParam('location'),
            true,
            $router->getParam('status_code')
        );
    }
}
