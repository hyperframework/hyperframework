<?php
namespace Hyperframework\Web\Test;

class ErrorView {
    public function render($statusCode, $statusText, $error) {
        echo $statusCode . ', ';
        echo $statusText . ', ';
        echo __METHOD__;
    }
}
