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
        Response::setHeader(
            'Location: ' . $app->getRouter()->getParam('location'),
            true,
            $app->getRouter()->getParam('status_code')
        );
    }
}
