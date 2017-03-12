<?php
namespace Hyperframework\Cli;

class OptionArgumentConfig {
    private $name;
    private $isRequired;
    private $values;

    /**
     * @param string $name
     * @param bool $isRequired
     * @param string[] $values
     */
    public function __construct($name, $isRequired, $values = null) {
        $this->name = $name;
        $this->isRequired = $isRequired;
        $this->values = $values;
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
     * @return string[]
     */
    public function getValues() {
        return $this->values;
    }
}
