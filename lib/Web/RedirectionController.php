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
        Response::getHeader(
            $app->getRouter()->getParam('location'),
            $app->getRouter()->getParam('status_code')
        );
    }
}
