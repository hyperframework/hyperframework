<?php
namespace Hyperframework\Common;

class ConfigEngine {
    private $data = [];
    private $appRootPath;

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null) {
        $segments = explode('.', $name);
        $node =& $this->data;
        foreach ($segments as $segment) {
            if (is_array($node) && isset($node[$segment])) {
                $node =& $node[$segment];
            } else {
                return $default;
            }
        }
        return $node;
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getString($name, $default = null) {
        $result = $this->get($name);
        if ($result === null) {
            return $default;
        }
        if (is_string($result)) {
            return $result;
        }
        if (is_scalar($result) || is_resource($result)) {
            return (string)$result;
        }
        if (is_object($result)) {
            if (method_exists($result, '__toString')) {
                return (string)$result;
            }
            throw new ConfigException(
                "Config '$name' requires a string, object of class "
                    . get_class($result) . " could not be converted to string."
            );
        }
        throw new ConfigException(
            "Config '$name' requires a string, "
                . gettype($result) . ' could not be converted to string.'
        );
    }

    /**
     * @param string $name
     * @param bool $default
     * @return bool
     */
    public function getBool($name, $default = null) {
        $result = $this->get($name);
        if ($result === null) {
            return $default;
        }
        return (bool)$result;
    }

    /**
     * @param string $name
     * @param int $default
     * @return int
     */
    public function getInt($name, $default = null) {
        $result = $this->get($name);
        if ($result === null) {
            return $default;
        }   
        if (is_object($result)) {
            throw new ConfigException(
                "Config '$name' requires an integer, object of class '"
                    . get_class($result)
                    . "' could not be converted to integer."
            );
        }
        return (int)$result;
    }

    /**
     * @param string $name
     * @param float $default
     * @return float
     */
    public function getFloat($name, $default = null) {
        $result = $this->get($name);
        if ($result === null) {
            return $default;
        }
        if (is_object($result)) {
            throw new ConfigException(
                "Config '$name' requires a float, object of class '"
                    . get_class($result) . "' could not be converted to float."
            );
        }
        return (float)$result;
    }

    /**
     * @param string $name
     * @param array $default
     * @return array
     */
    public function getArray($name, $default = null) {
        $result = $this->get($name);
        if ($result === null) {
            return $default;
        }
        if (is_array($result) === false) {
            throw new ConfigException(
                "Config '$name' requires an array, "
                    . gettype($result) . " given."
            );
        }
        return $result;
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getClass($name, $default = null) {
        $result = $this->getString($name);
        if ($result === null) {
            return $default;
        }
        if ($result === '') {
            throw new ConfigException(
                "The class name cannot be empty, set using config '$name'."
            );
        }
        if (class_exists($result) === false) {
            throw new ClassNotFoundException(
                "Class '$result' does not exist, set using config '$name'."
            );
        }
        return $result;
    }

    /**
     * @param string $name
     * @param callable $default
     * @return callable
     */
    public function getCallable($name, $default = null) {
        $result = $this->get($name);
        if ($result === null) {
            return $default;
        }
        if (is_callable($result) === false) {
            throw new ConfigException(
                "The value of config '$name' is not callable."
            );
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getAppRootPath() {
        $configName = 'hyperframework.app_root_path';
        $appRootPath = $this->getString($configName);
        if ($this->appRootPath === null
            || $appRootPath !== $this->appRootPath
        ) {
            if ($appRootPath === null) {
                throw new ConfigException(
                    "Config '$configName' does not exist."
                );
            }
            $isFullPath = FileFullPathRecognizer::isFullPath($appRootPath);
            if ($isFullPath === false) {
                throw new ConfigException(
                    "The value of config '$configName'"
                        . " must be a full path, '$appRootPath' given."
                );
            }
            $this->appRootPath = $appRootPath;
        }
        return $appRootPath;
    }

    /**
     * @return string
     */
    public function getAppRootNamespace() {
        return $this->getString('hyperframework.app_root_namespace', '');
    }

    /**
     * @return array
     */
    public function getAll() {
        return $this->data;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value) {
        $this->import([$name => $value]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name) {
        $segments = explode('.', $name);
        $node =& $this->data;
        foreach ($segments as $segment) {
            if (isset($node[$segment])) {
                $node =& $node[$segment];
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $name
     * @return void
     */
    public function remove($name) {
        $segments = explode('.', $name);
        $parent = null;
        $node =& $this->data;
        $segment = null;
        foreach ($segments as $segment) {
            if (isset($node[$segment])) {
                $parent =& $node;
                $node =& $node[$segment];
            } else {
                return;
            }
        }
        unset($parent[$segment]);
    }

    /**
     * @param array $data
     * @return void
     */
    public function import($data) {
        $this->build($this->data, $data);
        if (isset($this->data['php'])) {
            if (is_array($this->data['php']) === false) {
                $type = gettype($this->data['php']);
                throw new ConfigException(
                    "The value of config 'php' "
                        . "requires an array, $type given."
                );
            }
            foreach ($this->data['php'] as $name => $value) {
                $this->setPhpConfig($name, $value);
            }
            unset($this->data['php']);
        }
        if (isset($this->data['imports'])) {
            if (is_array($this->data['imports']) === false) {
                $type = gettype($this->data['imports']);
                throw new ConfigException(
                    "The value of config 'imports' "
                        . "requires an array, $type given."
                );
            }
            foreach ($this->data['imports'] as $path) {
                $this->importFile($path);
            }
            unset($this->data['imports']);
        }
    }

    /**
     * @param string $path
     * @return void
     */
    public function importFile($path) {
        $data = ConfigFileLoader::loadPhp($path);
        if ($data === null) {
            return;
        }
        if (is_array($data) === false) {
            throw new ConfigException(
                "Config file '$path' must return "
                    . " an array, " . gettype($data) . ' returned.'
            );
        }
        $this->import($data);
    }

    /**
     * @param array &$node
     * @param array $data
     * @return void
     */
    private function build(&$node, $data) {
        foreach ($data as $name => $value) {
            $segments = explode('.', $name);
            $currentNode =& $node;
            foreach ($segments as $segment) {
                if (is_array($currentNode) === false) {
                    $currentNode = [];
                }
                if (isset($currentNode[$segment]) === false) {
                    if ($segment === '') {
                        throw new ConfigException(
                            "The config name cannot be empty."
                        );
                    }
                    $currentNode[$segment] = [];
                }
                $currentNode =& $currentNode[$segment];
            }
            if (is_array($value)) {
                $this->build($currentNode, $value);
            } else {
                $currentNode = $value;
            }
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    private function setPhpConfig($name, $value) {
        if (is_array($value)) {
            foreach ($value as $childName => $childValue) {
                $this->setPhpConfig($name . '.' . $childName, $childValue);
            }
        } else {
            ini_set($name, $value);
        }
    }
}
