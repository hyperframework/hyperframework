<?php
namespace Hyperframework\Db\Test;

use Hyperframework\Db\DbActiveRecord as Base;

class Document extends Base {
    public function getId() {
        return $this->getColumn('id');
    }

    public function setName($name) {
        $this->setColumn('name', $name);
    }

    public function getName() {
        $this->getColumn('name');
    }
}
