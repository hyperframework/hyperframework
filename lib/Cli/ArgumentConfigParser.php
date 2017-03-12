<?php
namespace Hyperframework\Cli;

use Hyperframework\Common\ConfigException;

class ArgumentConfigParser {
    /**
     * @param array $configs
     * @param string $subcommandName
     * @return ArgumentConfig[]
     */
    public function parse($configs, $subcommandName = null) {
        $result = [];
        $hasRepeatableArgument = false;
        $optionalArgumentName = null;
        foreach ($configs as $config) {
            if (is_array($config) === false) {
                $type = gettype($config);
                throw new ConfigException($this->getErrorMessage(
                    $subcommandName,
                    null,
                    "config must be an array, $type given"
                ));
            }
            $isRequired = true;
            $isRepeatable = false;
            $name = null;
            foreach ($config as $key => $value) {
                switch ($key) {
                    case 'name':
                        $name = $value;
                        break;
                    case 'required':
                        $isRequired = $value;
                        break;
                    case 'repeatable':
                        $isRepeatable = $value;
                }
            }
            if ($name === null) {
                throw new ConfigException($this->getErrorMessage(
                    $subcommandName, null, "field 'name' is missing"
                ));
            }
            if (is_string($name) === false) {
                $type = gettype($name);
                throw new ConfigException($this->getErrorMessage(
                    $subcommandName,
                    null,
                    "the value of field"
                        . " 'name' must be a string, $type given"
                ));
            }
            if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*$/', $name) !== 1) {
                throw new ConfigException($this->getErrorMessage(
                    $subcommandName,
                    null,
                    "value '$name' of field 'name' is invalid"
                ));
            }
            if (is_bool($isRequired) === false) {
                $type = gettype($isRequired);
                throw new ConfigException($this->getErrorMessage(
                    $subcommandName,
                    $name,
                    "the value of field"
                        . " 'required' must be a boolean, $type given"
                ));
            }
            if ($optionalArgumentName !== null) {
                if ($isRequired) {
                    throw new ConfigException($this->getErrorMessage(
                        $subcommandName,
                        $optionalArgumentName,
                        'it cannot be optional'
                    ));
                }
            }
            if ($isRequired === false) {
                $optionalArgumentName = $name;
            }
            if (is_bool($isRepeatable) === false) {
                $type = gettype($isRepeatable);
                throw new ConfigException($this->getErrorMessage(
                    $subcommandName,
                    $name,
                    "the value of field"
                        . " 'repeatable' must be a boolean, $type given"
                ));
            }
            if ($hasRepeatableArgument) {
                throw new ConfigException($this->getErrorMessage(
                    $subcommandName,
                    $name,
                    'repeatable argument must be the last one'
                ));
            }
            $hasRepeatableArgument = $isRepeatable;
            $result[] = new ArgumentConfig($name, $isRequired, $isRepeatable);
        }
        return $result;
    }

    /**
     * @param string $subcommandName
     * @param string $name
     * @param string $extra
     * @return string
     */
    private function getErrorMessage($subcommandName, $name, $extra) {
        if ($subcommandName === null) {
            $result = 'Command';
        } else {
            $result = "Subcommand '$subcommandName'";
        }
        $result .= ' argument';
        if ($name !== null) {
            $result .= " '$name'";
        }
        return $result . ' config error, ' . $extra . '.';
    }
}
