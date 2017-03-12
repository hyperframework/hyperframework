<?php
namespace Hyperframework\Cli;

use Hyperframework\Common\Config;
use Hyperframework\Common\Inflector;
use ReflectionParameter;

class DefaultArgumentConfig extends ArgumentConfig {
    private $parameterName;
    private $name;

    /**
     * @param ReflectionParameter $reflectionParameter
     */
    public function __construct($reflectionParameter) {
        if (method_exists($reflectionParameter, 'isVariadic')
            && $reflectionParameter->isVariadic()
        ) {
            $isRepeatable = true;
            $isRequired = false;
        } else {
            $isRepeatable = false;
            $isRequired = !$reflectionParameter->isOptional();
        }
        parent::__construct(
            null,
            $isRequired,
            $isRepeatable
        );
        $this->parameterName = $reflectionParameter->getName();
    }

    /**
     * @return string
     */
    public function getName() {
        if ($this->name !== null) {
            return $this->name;
        }
        $words = [];
        $word = '';
        $length = strlen($this->parameterName);
        for ($index = 0; $index < $length; ++$index) {
            $char = $this->parameterName[$index];
            $ascii = ord($char);
            if ($char !== '_' && ($ascii < 65 || $ascii > 90)) {
                $word .= $this->parameterName[$index];
            } else {
                if ($word !== '') {
                    $words[] = $word;
                    $word = '';
                }
                if ($char !== '_') {
                    $word = strtolower($char);
                }
            }
        }
        if ($word !== '') {
            if ($this->isRepeatable() && ctype_alpha($word)) {
                if ($word !== 'list') {
                    $inflectorClass = Config::getClass(
                        'hyperframework.inflector_class', Inflector::class
                    );
                    $words[] = $inflectorClass::singularize($word);
                } elseif (count($words) === 0) {
                    $words[] = 'element';
                }
            } else {
                $words[] = $word;
            }
        }
        $this->name = implode('-', $words);
        return $this->name;
    }
}
