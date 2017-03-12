<?php
namespace Hyperframework\Cli;

class MutuallyExclusiveOptionGroupConfig {
    private $optionConfigs;
    private $isRequired;

    /**
     * @param OptionConfig[] $optionConfigs
     * @param bool $isRequired
     */
    public function __construct($optionConfigs, $isRequired) {
        $this->optionConfigs = $optionConfigs;
        $this->isRequired = $isRequired;
    }

    /**
     * @return OptionConfig[]
     */
    public function getOptionConfigs() {
        return $this->optionConfigs;
    }

    /**
     * @return bool
     */
    public function isRequired() {
        return $this->isRequired;
    }
}
