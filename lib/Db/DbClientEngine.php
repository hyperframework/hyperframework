<?php
namespace Hyperframework\Db;

use PDO;
use InvalidArgumentException;
use Closure;
use Hyperframework\Common\Config;

class DbClientEngine {
    private $connection;
    private $defaultConnectionName;
    private $connectionFactory;
    private $namedConnections = [];
    private $anonymousConnection;

    /**
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function findColumn($sql, $params = null) {
        $statement = $this->find($sql, $params);
        return $this->fetchColumn($statement);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param array $columns
     * @return mixed
     */
    public function findColumnByColumns($table, $columnName, $columns) {
        $statement = $this->findByColumns($table, $columns, [$columnName]);
        return $this->fetchColumn($statement);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param mixed $id
     * @return mixed
     */
    public function findColumnById($table, $columnName, $id) {
        $statement = $this->findByColumns($table, ['id' => $id], [$columnName]);
        return $this->fetchColumn($statement);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function findRow($sql, $params = null) {
        $statement = $this->find($sql, $params);
        return $this->fetchRow($statement);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $select
     * @return array
     */
    public function findRowByColumns($table, $columns, $select = null) {
        $statement = $this->findByColumns($table, $columns, $select);
        return $this->fetchRow($statement);
    }

    /**
     * @param string $table
     * @param mixed $id
     * @param array $select
     * @return array
     */
    public function findRowById($table, $id, $select = null) {
        $statement = $this->findByColumns($table, ['id' => $id], $select);
        return $this->fetchRow($statement);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array[]
     */
    public function findAll($sql, $params = null) {
        $statement = $this->find($sql, $params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $select
     * @return array[]
     */
    public function findAllByColumns($table, $columns, $select = null) {
        $statement = $this->findByColumns($table, $columns, $select);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return DbStatement
     */
    public function find($sql, $params = null) {
        return $this->sendSql($sql, $params, true);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $select
     * @return DbStatement
     */
    public function findByColumns($table, $columns, $select = null) {
        if ($select === null) {
            $select = '*';
        } else {
            if (count($select) === 0) {
                $select = '*';
            } else {
                foreach ($select as &$name) {
                    $name = $this->quoteIdentifier($name);
                }
                $select = implode(', ', $select);
            }
        }
        list($where, $params) = $this->buildWhereByColumns($columns);
        $sql = 'SELECT ' . $select . ' FROM ' . $this->quoteIdentifier($table);
        if ($where !== null) {
            $sql .= ' WHERE ' . $where;
        }
        return $this->find($sql, $params);
    }

    /**
     * @param string $table
     * @param string|array $where
     * @param array $params
     * @return int
     */
    public function count($table, $where = null, $params = null) {
        return (int)$this->calculate($table, '*', 'COUNT', $where, $params);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param string|array $where
     * @param array $params
     * @return mixed
     */
    public function min($table, $columnName, $where = null, $params = null) {
        return $this->calculate($table, $columnName, 'MIN', $where, $params);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param string|array $where
     * @param array $params
     * @return mixed
     */
    public function max($table, $columnName, $where = null, $params = null) {
        return $this->calculate($table, $columnName, 'MAX', $where, $params);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param string|array $where
     * @param array $params
     * @return mixed
     */
    public function sum($table, $columnName, $where = null, $params = null) {
        return $this->calculate($table, $columnName, 'SUM', $where, $params);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param string|array $where
     * @param array $params
     * @return mixed
     */
    public function average(
        $table, $columnName, $where = null, $params = null
    ) {
        return $this->calculate($table, $columnName, 'AVG', $where, $params);
    }

    /**
     * @param string $table
     * @param array $row
     * @return void
     */
    public function insert($table, $row) {
        $keys = [];
        foreach (array_keys($row) as $key) {
            $keys[] = $this->quoteIdentifier($key);
        }
        $columnCount = count($row);
        if ($columnCount > 0) {
            $placeHolders = str_repeat('?, ', $columnCount - 1) . '?';
        } else {
            $placeHolders = '';
        }
        $sql = 'INSERT INTO ' . $this->quoteIdentifier($table)
            . '(' . implode($keys, ', ') . ') VALUES(' . $placeHolders . ')';
        $this->execute($sql, array_values($row));
    }

    /**
     * @param string $table
     * @param array[] $rows
     * @param array $options
     * @return void
     */
    public function insertAll($table, $rows, $options = []) {
        $count = count($rows);
        if ($count === 0) {
            return;
        }
        $columnNames = null;
        if (isset($options['column_names'])) {
            $columnNames = $options['column_names'];
            if (is_array($columnNames) === false) {
                throw new InvalidArgumentException(
                    "The value of option 'column_names' must be an array, "
                        . gettype($columnNames) . ' given.'
                );
            }
        } else {
            if (is_array($rows[0]) === false) {
                throw new InvalidArgumentException(
                    "Row must be an array, "
                        . gettype($rows[0]) . " given at row 0."
                );
            }
            $columnNames = array_keys($rows[0]);
        }
        $columnCount = count($columnNames);
        if ($columnCount === 0) {
            return;
        }
        if (isset($options['batch_size'])) {
            $batchSize = (int)$options['batch_size'];
            if ($batchSize <= 0) {
                throw new InvalidArgumentException(
                    "The value of option 'batch_size' must be greater than 0, "
                        . $batchSize . ' given.'
                );
            }
        } else {
            $batchSize = $count;
        }
        foreach ($columnNames as &$columnName) {
            $columnName = $this->quoteIdentifier($columnName);
        }
        $prefix = 'INSERT INTO ' . $this->quoteIdentifier($table)
            . '(' . implode($columnNames, ', ') . ') VALUES';
        $placeHolders = '(' . str_repeat('?, ', $columnCount - 1) . '?)';
        $statement = null;
        $index = 0;
        while ($index < $count) {
            $values = [];
            $size = $batchSize;
            if ($index + $batchSize >= $count) {
                $size = $count - $index;
            }
            if ($statement === null || $size !== $batchSize) {
                $sql = $prefix . str_repeat($placeHolders . ',', $size - 1)
                    . $placeHolders;
                $statement = $this->prepare(
                    $sql, [PDO::ATTR_EMULATE_PREPARES => false]
                );
            }
            while ($size > 0) {
                if (is_array($rows[$index]) === false) {
                    throw new InvalidArgumentException(
                        "Row must be an array, "
                            . gettype($rows[0]) . " given at row $index."
                    );
                }
                if (count($rows[$index]) !== $columnCount) {
                    throw new InvalidArgumentException(
                        "Number of columns is invalid at row $index,"
                            . " expected $columnCount, actual "
                            . count($rows[$index]) . "."
                    );
                }
                $values = array_merge($values, array_values($rows[$index]));
                ++$index;
                --$size;
            }
            $statement->execute($values);
        }
    }

    /**
     * @param string $table
     * @param array $columns
     * @param string|array $where
     * @param array $params
     * @return int
     */
    public function update($table, $columns, $where, $params = null) {
        if (count($columns) === 0) {
            throw new InvalidArgumentException(
                "Arguemnt 'columns' cannot be an empty array."
            );
        }
        if (is_array($where)) {
            list($where, $params) = $this->buildWhereByColumns($where);
        }
        if ($where !== null) {
            $where = ' WHERE ' . $where;
            if ($params === null) {
                $params = [];
            }
            $params = array_merge(array_values($columns), $params);
        } else {
            $params = array_values($columns);
        }
        $tmp = [];
        foreach (array_keys($columns) as $key) {
            $tmp[] = $this->quoteIdentifier($key) . ' = ?';
        }
        $sql = 'UPDATE ' . $this->quoteIdentifier($table)
            . ' SET ' . implode(', ', $tmp) . $where;
        return $this->execute($sql, $params);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param mixed $id
     * @return bool
     */
    public function updateById($table, $columns, $id) {
        return $this->update($table, $columns, 'id = ?', [$id]) > 0;
    }

    /**
     * @param string $table
     * @param string|array $where
     * @param array $params
     * @return int
     */
    public function delete($table, $where, $params = null) {
        if (is_array($where)) {
            list($where, $params) = $this->buildWhereByColumns($where);
        }
        if ($where !== null) {
            $where = ' WHERE ' . $where;
        }
        $sql = 'DELETE FROM ' . $this->quoteIdentifier($table) . $where;
        return $this->execute($sql, $params);
    }

    /**
     * @param string $table
     * @param mixed $id
     * @return bool
     */
    public function deleteById($table, $id) {
        return $this->delete($table, 'id = ?', [$id]) > 0;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function execute($sql, $params = null) {
        return $this->sendSql($sql, $params);
    }

    /**
     * @return mixed
     */
    public function getLastInsertId() {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * @return void
     */
    public function beginTransaction() {
        $this->getConnection()->beginTransaction();
    }

    /**
     * @return void
     */
    public function commit() {
        $this->getConnection()->commit();
    }

    /**
     * @return void
     */
    public function rollback() {
        $this->getConnection()->rollBack();
    }

    /**
     * @return bool
     */
    public function inTransaction() {
        return $this->getConnection()->inTransaction();
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier) {
        return $this->getConnection()->quoteIdentifier($identifier);
    }

    /**
     * @param string $sql
     * @param array $driverOptions
     * @return DbStatement
     */
    public function prepare($sql, $driverOptions = []) {
        return $this->getConnection()->prepare($sql, $driverOptions);
    }

    /**
     * @param string $name
     * @return void
     */
    public function connect($name = null) {
        if ($name === null) {
            if ($this->defaultConnectionName === null) {
                $name = Config::getString(
                    'hyperframework.db.default_connection_name'
                );
            } else {
                $name = $this->defaultConnectionName;
            }
        }
        if ($name === null) {
            if ($this->anonymousConnection !== null) {
                $this->connection = $this->anonymousConnection;
                return;
            }
        } else {
            if (isset($this->namedConnections[$name])) {
                $this->connection = $this->namedConnections[$name];
                return;
            }
        }
        $factory = $this->getConnectionFactory();
        $this->connection = $factory->createConnection($name);
        if ($name === null) {
            $this->anonymousConnection = $this->connection;
        } else {
            $this->namedConnections[$name] = $this->connection;
        }
    }

    /**
     * @param string $name
     * @return void
     */
    public function removeConnection($name = null) {
        if ($name === null) {
            if ($this->connection === null) {
                return;
            }
            $name = $this->connection->getName();
            $this->connection = null;
        } elseif ($this->connection !== null) {
            if ($this->connection->getName() === $name) {
                $this->connection = null;
            }
        }
        if ($name === null) {
            $this->anonymousConnection = null;
        } elseif (isset($this->namedConnections[$name])) {
            unset($this->namedConnections[$name]);
        }
    }

    /**
     * @param DbConnection|string $connectionOrConnectionName
     * @param Closure $callback
     * @return mixed
     */
    public function useConnection($connectionOrConnectionName, $callback) {
        $previousConnection = $this->getConnection(false);
        $previousDefaultConnectionName = $this->defaultConnectionName;
        try {
            if (is_object($connectionOrConnectionName)) {
                $this->setConnection($connectionOrConnectionName);
                $this->defaultConnectionName = null;
            } else {
                $this->setConnection(null);
                $this->defaultConnectionName = $connectionOrConnectionName;
            }
            return $callback();
        } finally {
            $this->setConnection($previousConnection);
            $this->defaultConnectionName = $previousDefaultConnectionName;
        }
    }

    /**
     * @return void
     */
    public function removeAllConnections() {
        $this->connection = null;
        $this->namedConnections = [];
        $this->anonymousConnection = null;
    }

    /**
     * @param DbConnection $connection
     * @return void
     */
    public function setConnection($connection) {
        $this->connection = $connection;
    }

    /**
     * @param bool $shouldConnect
     * @return DbConnection
     */
    public function getConnection($shouldConnect = true) {
        if ($this->connection === null && $shouldConnect) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * @param DbStatement $statement
     * @return mixed
     */
    private function fetchColumn($statement) {
        $row = $statement->fetch(PDO::FETCH_NUM);
        if (is_array($row) && isset($row[0])) {
            return $row[0];
        }
    }

    /**
     * @param DbStatement $statement
     * @return array
     */
    private function fetchRow($statement) {
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)) {
            return $row;
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @param bool $isQuery
     * @return mixed
     */
    private function sendSql($sql, $params = null, $isQuery = false) {
        $connection = $this->getConnection();
        if ($params === null || count($params) === 0) {
            return $isQuery ?
                $connection->query($sql) : $connection->exec($sql);
        }
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        if ($isQuery) {
            return $statement;
        }
        return $statement->rowCount();
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param string $function
     * @param string|array $where
     * @param array $params
     * @return mixed
     */
    private function calculate(
        $table, $columnName, $function, $where, $params = null
    ) {
        $table = $this->quoteIdentifier($table);
        if ($columnName !== '*') {
            $columnName = $this->quoteIdentifier($columnName);
        }
        if (is_array($where)) {
            list($where, $params) = $this->buildWhereByColumns($where);
        }
        $sql = 'SELECT ' . $function . '(' . $columnName . ') FROM ' . $table;
        if ($where !== null) {
            $sql .= ' WHERE ' . $where;
        }
        return $this->findColumn($sql, $params);
    }

    /**
     * @param array $columns
     * @return array
     */
    private function buildWhereByColumns($columns) {
        $params = [];
        $where = null;
        foreach ($columns as $key => $value) {
            $params[] = $value;
            if ($where !== null) {
                $where .= ' AND ';
            }
            $where .= $this->quoteIdentifier($key) . ' = ?';
        }
        return [$where, $params];
    }

    /**
     * @return DbConnectionFactory
     */
    private function getConnectionFactory() {
        if ($this->connectionFactory === null) {
            $class = Config::getClass(
                'hyperframework.db.connection_factory_class',
                DbConnectionFactory::class
            );
            $this->connectionFactory = new $class;
        }
        return $this->connectionFactory;
    }
}
