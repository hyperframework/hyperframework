<?php
namespace Hyperframework\Db;

use Hyperframework\Common\Config;
use Hyperframework\Common\ConfigFileLoader;
use Hyperframework\Common\ConfigException;

class DbConnectionFactory {
    private $config;

    /**
     * @param string $name
     * @return DbConnection
     */
    public function createConnection($name = null) {
        $config = $this->getConfig($name);
        $dsn = isset($config['dsn']) ? $config['dsn'] : null;
        $username = isset($config['username']) ? $config['username'] : null;
        $password = isset($config['password']) ? $config['password'] : null;
        $options = isset($config['options']) ? $config['options'] : [];
        $class = Config::getClass(
            'hyperframework.db.connection_class', DbConnection::class
        );
        $connection = new $class($name, $dsn, $username, $password, $options);
        return $connection;
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getConfig($name) {
        if ($this->config === null) {
            $this->config = ConfigFileLoader::loadPhp(
                Config::getString('hyperframework.db.config_path', 'db.php')
            );
        }
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
        if ($name === null) {
            return $this->config;
        }
    }
}
