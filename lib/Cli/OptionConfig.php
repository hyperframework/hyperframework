<?php
namespace Hyperframework\Cli;

class OptionConfig {
    private $name;
    private $shortName;
    private $description;
    private $isRepeatable;
    private $isRequired;
    private $argumentConfig;

    /**
     * @param string $name
     * @param string $shortName
     * @param bool $isRequired
     * @param bool $isRepeatable
     * @param OptionArgumentConfig $argumentConfig
     * @param string $description
     */
    public function __construct(
        $name,
        $shortName,
        $isRequired,
        $isRepeatable,
        $argumentConfig,
        $description
    ) {
        $this->name = $name;
        $this->shortName = $shortName;
        $this->description = $description;
        $this->isRepeatable = $isRepeatable;
        $this->isRequired = $isRequired;
        $this->argumentConfig = $argumentConfig;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getShortName() {
        return $this->shortName;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isRepeatable() {
        return $this->isRepeatable;
    }

    /**
     * @return bool
     */
    public function isRequired() {
        return $this->isRequired;
    }

    /**
     * @return OptionArgumentConfig
     */
    public function getArgumentConfig() {
        return $this->argumentConfig;
    }
}
