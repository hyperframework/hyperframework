<?php
namespace Hyperframework\Cli;

use Hyperframework\Common\ConfigException;

class OptionConfigParser {
    /**
     * @param array $configs
     * @param bool $isMultipleCommandMode
     * @param string $subcommandName
     * @return OptionConfig[]
     */
    public function parse(
        $configs, $isMultipleCommandMode = false, $subcommandName = null
    ) {
        $result = [];
        $optionNames = [];
        foreach ($configs as $config) {
            if (is_array($config) === false) {
                $type = gettype($config);
                throw new ConfigException($this->getErrorMessage(
                    $isMultipleCommandMode,
                    $subcommandName,
                    null,
                    null,
                    "config must be an array, $type given"
                ));
            }
            $name = null;
            $shortName = null;
            $isRequired = false;
            $isRepeatable = false;
            $description = null;
            foreach ($config as $key => $value) {
                switch ($key) {
                    case 'name':
                        $name = $value;
                        break;
                    case 'short_name':
                        $shortName = $value;
                        break;
                    case 'required':
                        $isRequired = $value;
                        break;
                    case 'repeatable':
                        $isRepeatable = $value;
                        break;
                    case 'description':
                        $description = $value;
                }
            }
            if ($name !== null) {
                if (is_string($name) === false) {
                    $type = gettype($name);
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        null,
                        null,
                        "the value of field"
                            . " 'name' must be a string, $type given"
                    ));
                }
                if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*$/', $name) !== 1) {
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        null,
                        null,
                        "value '$name' of field 'name' is invalid"
                    ));
                }
            } else {
                throw new ConfigException($this->getErrorMessage(
                    $isMultipleCommandMode,
                    $subcommandName,
                    null,
                    null,
                    "field 'name' is requried"
                ));
            }
            if ($shortName !== null) {
                if (is_string($shortName) === false) {
                    $type = gettype($shortName);
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        $name,
                        null,
                        "the value of field"
                            . " 'short_name' must be a string, $type given"
                    ));
                }
                if (strlen($shortName) !== 1
                    || ctype_alnum($shortName) === false
                ) {
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        $name,
                        null,
                        "value '$shortName' of "
                            . "field 'short_name' is invalid"
                    ));
                }
                if ($shortName === 'W') {
                    throw new ConfigException(
                        'The -W (capital-W) option must be reserved for'
                            . ' implementation extensions.'
                    );
                }
            }
            if (strlen($name) === 1
                && $shortName !== null
                && $name !== $shortName
            ) {
                throw new ConfigException($this->getErrorMessage(
                    $isMultipleCommandMode,
                    $subcommandName,
                    $name,
                    null,
                    "values conflict between field 'name' and 'short_name'"
                ));
            }
            if (is_bool($isRequired) === false) {
                $type = gettype($isRequired);
                throw new ConfigException($this->getErrorMessage(
                    $isMultipleCommandMode,
                    $subcommandName,
                    $name,
                    $shortName,
                    "the value of field"
                        . " 'required' must be a boolean, $type given"
                ));
            }
            if (is_bool($isRepeatable) === false) {
                $type = gettype($isRepeatable);
                throw new ConfigException($this->getErrorMessage(
                    $isMultipleCommandMode,
                    $subcommandName,
                    $name,
                    $shortName,
                    "the value of field"
                        . " 'repeatable' must be a boolean, $type given"
                ));
            }
            $argumentConfig = null;
            if (isset($config['argument'])) {
                if (is_array($config['argument']) === false) {
                    $type = gettype($config['argument']);
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        $name,
                        $shortName,
                        "the value of field 'argument' must be an array,"
                            . " $type given"
                    ));
                }
                $argumentConfig = $this->parseArgumentConfig(
                    $config['argument'],
                    $isMultipleCommandMode,
                    $subcommandName,
                    $name,
                    $shortName
                );
            }
            if ($description !== null) {
                if (is_string($description) === false) {
                    $type = gettype($description);
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        $name,
                        $shortName,
                        "the value of field"
                            . " 'description' must be a string, $type given"
                    ));
                }
            }
            $optionConfig = new OptionConfig(
                $name,
                $shortName,
                $isRequired,
                $isRepeatable,
                $argumentConfig,
                $description
            );
            if ($name !== null) {
                if (isset($optionNames[$name])) {
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        $name,
                        null,
                        "it has already been defined"
                    ));
                }
                $optionNames[$name] = true;
            }
            if ($shortName !== null) {
                if (isset($optionNames[$shortName])) {
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        null,
                        $shortName,
                        "it has already been defined"
                    ));
                }
                $optionNames[$shortName] = true;
            }
            $result[] = $optionConfig;
        }
        return $result;
    }

    /**
     * @param array $config
     * @param bool $isMultipleCommandMode
     * @param string $subcommandName
     * @param string $optionName
     * @param string $optionShortName
     * @return OptionArgumentConfig
     */
    private function parseArgumentConfig(
        $config,
        $isMultipleCommandMode,
        $subcommandName,
        $optionName,
        $optionShortName
    ) {
        if (is_array($config) === false) {
            $type = gettype($config);
            throw new ConfigException($this->getErrorMessage(
                $isMultipleCommandMode,
                $subcommandName,
                $optionName,
                $optionShortName,
                "the value of field 'argument' must be an array, $type given"
            ));
        }
        $name = null;
        $isRequired = true;
        $values = null;
        foreach ($config as $key => $value) {
            switch ($key) {
                case 'name':
                    $name = $value;
                    break;
                case 'required':
                    $isRequired = $value;
                    break;
                case 'values':
                    $values = $value;
            }
        }
        if ($name === null) {
            throw new ConfigException($this->getErrorMessage(
                $isMultipleCommandMode,
                $subcommandName,
                $optionName,
                $optionShortName,
                "option argument config field 'name' is missing or equals null"
            ));
        }
        if (is_string($name) === false) {
            $type = gettype($name);
            throw new ConfigException($this->getErrorMessage(
                $isMultipleCommandMode,
                $subcommandName,
                $optionName,
                $optionShortName,
                "the value of option argument config field"
                    . " 'name' must be a string, $type given"
            ));
        }
        if (preg_match('/^[a-zA-Z0-9-]+$/', $name) !== 1) {
            throw new ConfigException($this->getErrorMessage(
                $isMultipleCommandMode,
                $subcommandName,
                $optionName,
                $optionShortName,
                "value '$name' of option argument config"
                    . " field 'name' is invalid"
            ));
        }
        if (is_bool($isRequired) === false) {
            $type = gettype($isRequired);
            throw new ConfigException($this->getErrorMessage(
                $isMultipleCommandMode,
                $subcommandName,
                $optionName,
                $optionShortName,
                "the value of option argument config field"
                    . " 'required' must be a boolean, $type given."
            ));
        }
        if ($values !== null) {
            if (is_array($values) === false) {
                $type = gettype($values);
                throw new ConfigException($this->getErrorMessage(
                    $isMultipleCommandMode,
                    $subcommandName,
                    $optionName,
                    $optionShortName,
                    "the value of option argument config field"
                        . " 'values' must be an array , $type given."
                ));
            }
            foreach ($values as &$value) {
                if (is_string($value) === false) {
                    $type = gettype($value);
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        $optionName,
                        $optionShortName,
                        "the element of option argument config field"
                            . " 'values' must be a string, $type given."
                    ));
                }
                if (preg_match('/^[a-zA-Z0-9-_]+$/', $value) !== 1) {
                    throw new ConfigException($this->getErrorMessage(
                        $isMultipleCommandMode,
                        $subcommandName,
                        $optionName,
                        $optionShortName,
                        "element '$value' of option argument config field"
                            . " 'values' is invalid."
                    ));
                }
            }
        }
        return new OptionArgumentConfig($name, $isRequired, $values);
    }

    /**
     * @param bool $isMultipleCommandMode
     * @param string $subcommandName
     * @param string $name
     * @param string $shortName
     * @param string $extra
     * @return string
     */
    private function getErrorMessage(
        $isMultipleCommandMode, $subcommandName, $name, $shortName, $extra
    ) {
        if ($subcommandName === null) {
            if ($isMultipleCommandMode) {
                $result = 'Global command';
            } else {
                $result = 'Command';
            }
        } else {
            $result = "Subcommand '$subcommandName'";
        }
        $result .= ' option';
        if ($name !== null) {
            $result .= " '$name'";
        } elseif ($shortName !== null) {
            $result .= " '$shortName'";
        }
        return $result . ' config error, ' . $extra . '.';
    }
}
