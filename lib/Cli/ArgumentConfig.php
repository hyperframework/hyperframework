<?php
namespace Hyperframework\Cli;

class ArgumentConfig {
    private $name;
    private $isRequired;
    private $isRepeatable;

    /**
     * @param string $name
     * @param bool $isRequired
     * @param bool $isRepeatable
     */
    public function __construct($name, $isRequired, $isRepeatable) {
        $this->name = $name;
        $this->isRequired = $isRequired;
        $this->isRepeatable = $isRepeatable;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isRequired() {
        return $this->isRequired;
    }

    /**
     * @return bool
     */
    public function isRepeatable() {
        return $this->isRepeatable;
    }
}
