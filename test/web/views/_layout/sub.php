<?php
$this->setLayout('_layout/main.php');
$this->setBlock('content', function() {
    echo 'begin-sub ';
    $this->renderBlock('subcontent');
    echo ' end-sub';
});
