<?php
namespace Controllers;

use Hyperframework\Web\Controller as Base;
use Hyperframework\Web\Response;
use UnexpectedValueException;

class Controller extends Base {
    public function renderView() {
        if ($this->isViewEnabled() === false) {
            return;
        }
        Response::setHeader('Content-Type: application/json');
        $json = json_encode(
            $this->getActionResult(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($json === false) {
            throw new UnexpectedValueException('The action result is invalid.');
        }
        echo $json;
    }
}
