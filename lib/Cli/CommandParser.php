<?php
namespace Hyperframework\Cli;

class CommandParser {
    /**
     * @param CommandConfig $commandConfig
     * @param string[] $argv
     * @return array
     */
    public function parse($commandConfig, $argv = null) {
        if ($argv === null) {
            $argv = $_SERVER['argv'];
        }
        $result = [];
        $subcommandName = null;
        $optionType = null;
        if ($commandConfig->isMultipleCommandMode()) {
            $result['global_options'] = [];
            $optionType = 'global_options';
        } else {
            $result['options'] = [];
            $result['arguments'] = [];
            $optionType = 'options';
        }
        $isGlobal = $commandConfig->isMultipleCommandMode();
        $count = count($argv);
        $isArgument = false;
        $arguments = [];
        for ($index = 1; $index < $count; ++$index) {
            $element = $argv[$index];
            $length = strlen($element);
            if ($length === 0
                || $element[0] !== '-'
                || $element === '-'
                || $isArgument
            ) {
                if ($isGlobal) {
                    if ($commandConfig->hasSubcommand($element) === false) {
                        throw new CommandParsingException(
                            "Subcommand '$element' does not exist."
                        );
                    }
                    $isGlobal = false;
                    $subcommandName = $element;
                    $result['subcommand_name'] = $element;
                    $result['options'] = [];
                    $optionType = 'options';
                } else {
                    $arguments[] = $element;
                }
                continue;
            }
            if ($element === '--') {
                if ($isGlobal) {
                    throw new CommandParsingException(
                        "Option '--' is not allowed."
                    );
                }
                $isArgument = true;
                continue;
            }
            if ($element[1] !== '-') {
                $charIndex = 1;
                while ($length > 1) {
                    $optionShortName = $element[$charIndex];
                    $optionConfig = $commandConfig->getOptionConfig(
                        $optionShortName, $subcommandName
                    );
                    if ($optionConfig === null) {
                        $message = "Option '$optionShortName' is not allowed.";
                        throw new CommandParsingException(
                            $message, $subcommandName
                        );
                    }
                    $optionArgument = true;
                    $optionArgumentConfig = $optionConfig->getArgumentConfig();
                    $hasArgument = -1;
                    if ($optionArgumentConfig !== null) {
                        $hasArgument = $optionArgumentConfig->isRequired() ?
                            1 : 0;
                    }
                    if ($hasArgument === 0) {
                        if ($length > 2) {
                            $optionArgument = substr($element, 1 + $charIndex);
                        }
                    } elseif ($hasArgument === 1) {
                        if ($length > 2) {
                            $optionArgument = substr($element, 1 + $charIndex);
                        } else {
                            ++$index;
                            if ($index >= $count) {
                                $message = 'Option \'' . $optionShortName
                                    . '\' must have an argument.';
                                throw new CommandParsingException(
                                    $message, $subcommandName
                                );
                            }
                            $optionArgument = $argv[$index];
                        }
                    }
                    $this->addOption(
                        $result[$optionType], $optionArgument, $optionConfig
                    );
                    if ($optionArgument !== true) {
                        break;
                    }
                    ++$charIndex;
                    --$length;
                }
            } else {
                $optionArgument = true;
                $optionName = $element;
                if (strpos($element, '=') !== false) {
                    list($optionName, $optionArgument) =
                        explode('=', $element, 2);
                }
                $optionName = substr($optionName, 2);
                $optionConfig = $commandConfig->getOptionConfig(
                    $optionName, $subcommandName
                );
                if ($optionConfig === null) {
                    $message = "Unknown option '$optionName'.";
                    throw new CommandParsingException(
                        $message, $subcommandName
                    );
                }
                $optionArgumentConfig = $optionConfig->getArgumentConfig();
                $hasArgument = -1;
                if ($optionArgumentConfig !== null) {
                    $hasArgument = $optionArgumentConfig->isRequired() ?
                        1 : 0;
                }
                if ($hasArgument === 1) {
                    if ($optionArgument === true) {
                        ++$index;
                        if ($index >= $count) {
                            $message =
                                "Option '$optionName' must have an argument.";
                            throw new CommandParsingException(
                                $message, $subcommandName
                            );
                        }
                        $optionArgument = $argv[$index];
                    }
                } elseif ($hasArgument === -1) {
                    if ($optionArgument !== true) {
                        $message =
                            "Option '$optionName' must not have an argument.";
                        throw new CommandParsingException(
                            $message, $subcommandName
                        );
                    }
                }
                $this->addOption(
                    $result[$optionType], $optionArgument, $optionConfig
                );
            }
        }
        $hasMagicOption = $this->hasMagicOption(
            isset($result['global_options']) ? $result['global_options'] : null,
            isset($result['options']) ? $result['options'] : null,
            $commandConfig
        );
        if (isset($result['global_options'])) {
            $this->checkOptions(
                null,
                $result['global_options'],
                $commandConfig,
                $hasMagicOption
            );
        }
        if (isset($result['options'])) {
            $this->checkOptions(
                $subcommandName,
                $result['options'],
                $commandConfig,
                $hasMagicOption
            );
        }
        if ($subcommandName !== null) {
            $result['subcommand_name'] = $subcommandName;
        }
        if ($isGlobal || $hasMagicOption) {
            return $result;
        }
        $result['arguments'] = [];
        $argumentConfigs = $commandConfig->getArgumentConfigs($subcommandName);
        $argumentConfigCount = count($argumentConfigs);
        $argumentCount = count($arguments);
        for ($argumentIndex = 0;
            $argumentIndex < $argumentCount;
            ++$argumentIndex
        ) {
            if ($argumentConfigCount > $argumentIndex) {
                $result['arguments'][] = $arguments[$argumentIndex];
            } else {
                $argumentConfig = end($argumentConfigs);
                if ($argumentConfig !== false 
                    && $argumentConfig->isRepeatable()
                ) {
                    $result['arguments'][] = $arguments[$argumentIndex];
                } else {
                    throw new CommandParsingException(
                        'Number of arguments error.', $subcommandName
                    );
                }
            }
        }
        $count = 0;
        foreach ($argumentConfigs as $argumentConfig) {
            if ($argumentConfig->isRequired() === false) {
                break;
            }
            ++$count;
            if ($count > $argumentCount) {
                throw new CommandParsingException(
                    'Number of arguments error.', $subcommandName
                );
            }
        }
        return $result;
    }

    /**
     * @param array &$options
     * @param string $value
     * @param OptionConfig $optionConfig
     * @return void
     */
    private function addOption(&$options, $value, $optionConfig) {
        $name = $optionConfig->getName();
        if ($optionConfig->isRepeatable()) {
            if (isset($options[$name]) === false) {
                $options[$name] = [$value];
            } else {
                $options[$name][] = $value;
            }
        } else {
            $options[$name] = $value;
        }
    }

    /**
     * @param string[] $globalOptions
     * @param string[] $options
     * @param CommandConfig $commandConfig
     * @return bool
     */
    private function hasMagicOption(
        $globalOptions = null, $options = null, $commandConfig
    ) {
        if ($commandConfig->isMultipleCommandMode()) {
            if ($globalOptions !== null) {
                foreach (['help', 'version'] as $optionName) {
                    if (isset($globalOptions[$optionName])) {
                        return true;
                    }
                }
            }
            if ($options !== null) {
                if (isset($options['help'])) {
                    return true;
                }
            }
        } else {
            if ($options !== null) {
                foreach (['help', 'version'] as $optionName) {
                    if (isset($options[$optionName])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param string $subcommandName
     * @param string[] $options
     * @param CommandConfig $commandConfig
     * @param bool $hasMagicOption
     * @return void
     */
    private function checkOptions(
        $subcommandName, $options, $commandConfig, $hasMagicOption
    ) {
        $optionConfigs = $commandConfig->getOptionConfigs($subcommandName);
        foreach ($optionConfigs as $optionConfig) {
            if ($optionConfig->isRequired()) {
                $name = $optionConfig->getName();
                if (isset($options[$name])) {
                    continue;
                }
                if ($hasMagicOption === false) {
                    $message = "Option '$name' is required.";
                    throw new CommandParsingException(
                        $message, $subcommandName
                    );
                }
            }
        }
        foreach ($options as $name => $value) {
            $optionConfig = $commandConfig->getOptionConfig(
                $name, $subcommandName
            );
            $argumentConfig = $optionConfig->getArgumentConfig();
            if ($argumentConfig !== null) {
                $values = $argumentConfig->getValues();
                if ($values !== null) {
                    if (in_array($value, $values, true) === false) {
                        $message = "The value of option '$name' is invalid.";
                        throw new CommandParsingException(
                            $message, $subcommandName
                        );
                    }
                }
            }
        }
        $mutuallyExclusiveOptionGroupConfigs = $commandConfig
            ->getMutuallyExclusiveOptionGroupConfigs($subcommandName);
        if ($mutuallyExclusiveOptionGroupConfigs !== null) {
            foreach($mutuallyExclusiveOptionGroupConfigs as $groupConfig) {
                $optionName = null;
                $optionNames = [];
                foreach ($groupConfig->getOptionConfigs() as $optionConfig) {
                    $name = $optionConfig->getName();
                    if (isset($options[$name])) {
                        if ($optionName !== null && $optionName !== $name) {
                            $message = "The option '$optionName' and '$name'"
                                . " are mutually exclusive.";
                            throw new CommandParsingException(
                                $message, $subcommandName
                            );
                        }
                        $optionName = $name;
                    }
                    $optionNames[] = "'" . $name . "'";
                }
                if ($groupConfig->isRequired() && $optionName === null) {
                    if ($hasMagicOption === false && count($optionNames) !== 0)
                    {
                        $message = "One of option "
                            . implode(' or ', $optionNames) . " is required.";
                        throw new CommandParsingException(
                            $message, $subcommandName
                        );
                    }
                }
            }
        }
    }
}
