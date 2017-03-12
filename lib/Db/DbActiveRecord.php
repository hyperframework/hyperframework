<?php
namespace Hyperframework\Db;

use InvalidArgumentException;

abstract class DbActiveRecord {
    private static $tableNames = [];
    private $row = [];

    /**
     * @param array|string $where
     * @param array $params
     * @return static
     */
    public static function find($where, $params = null) {
        if (is_array($where)) {
            $row = DbClient::findRowByColumns(static::getTableName(), $where);
        } elseif (is_string($where) || $where === null) {
            $row = DbClient::findRow(
                self::completeSelectSql($where),
                $params
            );
        } else {
            $type = gettype($where);
            throw new InvalidArgumentException(
                "Argument 'where' must be a string or an array, $type given."
            );
        }
        if ($row === null) {
            return;
        }
        return static::fromArray($row);
    }

    /**
     * @param int $id
     * @return static
     */
    public static function findById($id) {
        $row = DbClient::findRowById(static::getTableName(), $id);
        if ($row === null) {
            return;
        }
        return static::fromArray($row);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return static
     */
    public static function findBySql($sql, $params = null) {
        $row = DbClient::findRow($sql, $params);
        if ($row === null) {
            return;
        }
        return static::fromArray($row);
    }

    /**
     * @param array|string $where
     * @param array $params
     * @return static[]
     */
    public static function findAll($where = null, $params = null) {
        if (is_array($where)) {
            $rows = DbClient::findAllByColumns(static::getTableName(), $where);
        } elseif (is_string($where) || $where === null) {
            $rows = DbClient::findAll(
                self::completeSelectSql($where), $params
            );
        } else {
            $type = gettype($where);
            throw new InvalidArgumentException(
                "Argument 'where' must be a string or an array, $type given."
            );
        }
        $result = [];
        foreach ($rows as $row) {
            $result[] = static::fromArray($row);
        }
        return $result;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return static[]
     */
    public static function findAllBySql($sql, $params = null) {
        $rows = DbClient::findAll($sql, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = static::fromArray($row);
        }
        return $result;
    }

    /**
     * @param array|string $where
     * @param array $params
     * @return int
     */
    public static function count($where = null, $params = null) {
        return DbClient::count(static::getTableName(), $where, $params);
    }

    /**
     * @param string $columnName
     * @param array|string $where
     * @param array $params
     * @return mixed
     */
    public static function min(
        $columnName, $where = null, $params = null
    ) {
        return DbClient::min(
            static::getTableName(),
            $columnName,
            $where,
            $params
        );
    }

    /**
     * @param string $columnName
     * @param array|string $where
     * @param array $params
     * @return mixed
     */
    public static function max($columnName, $where = null, $params = null) {
        return DbClient::max(
            static::getTableName(),
            $columnName,
            $where,
            $params
        );
    }

    /**
     * @param string $columnName
     * @param array|string $where
     * @param array $params
     * @return mixed
     */
    public static function sum($columnName, $where = null, $params = null) {
        return DbClient::sum(
            static::getTableName(),
            $columnName,
            $where,
            $params
        );
    }

    /**
     * @param string $columnName
     * @param array|string $where
     * @param array $params
     * @return mixed
     */
    public static function average(
        $columnName, $where = null, $params = null
    ) {
        return DbClient::average(
            static::getTableName(),
            $columnName,
            $where,
            $params
        );
    }

    /**
     * @param array $row
     * @return static
     */
    public static function fromArray($row) {
        $result = new static;
        $result->setRow($row);
        return $result;
    }

    /**
     * @return string
     */
    public static function getTableName() {
        $class = static::class;
        if (isset(self::$tableNames[$class]) === false) {
            $position = strrpos($class, '\\');
            if ($position !== false) {
                self::$tableNames[$class] = substr($class, $position + 1);
            }
        }
        return self::$tableNames[$class];
    }

    /**
     * @return void
     */
    public function insert() {
        DbClient::insert(static::getTableName(), $this->getRow());
        if ($this->hasColumn('id') === false) {
            $this->setColumn('id', DbClient::getLastInsertId());
        }
    }

    /**
     * @return void
     */
    public function update() {
        $row = $this->getRow();
        if (isset($row['id'])) {
            $id = $row['id'];
            if (count($row) > 1) {
                unset($row['id']);
                DbClient::updateById(static::getTableName(), $row, $id);
            }
        } else {
            $class = static::class;
            throw new DbActiveRecordException(
                "Cannot update active record '$class' which is not persistent, "
                    . "because column 'id' is missing."
            );
        }
    }

    /**
     * @return void
     */
    public function delete() {
        if ($this->hasColumn('id')) {
            DbClient::deleteById(
                static::getTableName(), $this->getColumn('id')
            );
        } else {
            $class = static::class;
            throw new DbActiveRecordException(
                "Cannot delete active record '$class' which is not persistent, "
                    . "because column 'id' is missing."
            );
        }
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->getRow();
    }

    /**
     * @return array
     */
    protected function getRow() {
        return $this->row;
    }

    /**
     * @param array $row
     * @return void
     */
    protected function setRow($row) {
        $this->row = $row;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getColumn($name) {
        if (isset($this->row[$name])) {
            return $this->row[$name];
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function setColumn($name, $value) {
        $this->row[$name] = $value;
    }

    /**
     * @param array $columns
     * @return void
     */
    protected function setColumns($columns) {
        foreach ($columns as $name => $value) {
            $this->setColumn($name, $value);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function hasColumn($name) {
        return isset($this->row[$name]);
    }

    /**
     * @param string $name
     * @return void
     */
    protected function removeColumn($name) {
        unset($this->row[$name]);
    }

    /**
     * @param string $where
     * @return string
     */
    private static function completeSelectSql($where) {
        $result = 'SELECT * FROM '
            . DbClient::quoteIdentifier(static::getTableName());
        $where = (string)$where;
        if ($where !== '') {
            $result .= ' WHERE ' . $where;
        }
        return $result;
    }
}
