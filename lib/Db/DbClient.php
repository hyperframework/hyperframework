<?php
namespace Hyperframework\Db;

use Hyperframework\Common\Registry;
use Hyperframework\Common\Config;
use Closure;

class DbClient {
    /**
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public static function findColumn($sql, $params = null) {
        return static::getEngine()->findColumn($sql, $params);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param array $columns
     * @return mixed
     */
    public static function findColumnByColumns($table, $columnName, $columns) {
        return static::getEngine()->findColumnByColumns(
            $table, $columnName, $columns
        );
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param mixed $id
     * @return mixed
     */
    public static function findColumnById($table, $columnName, $id) {
        return static::getEngine()->findColumnById($table, $columnName, $id);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function findRow($sql, $params = null) {
        return static::getEngine()->findRow($sql, $params);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $select
     * @return array
     */
    public static function findRowByColumns($table, $columns, $select = null) {
        return static::getEngine()->findRowByColumns($table, $columns, $select);
    }

    /**
     * @param string $table
     * @param mixed $id
     * @param array $select
     * @return array
     */
    public static function findRowById($table, $id, $select = null) {
        return static::getEngine()->findRowById($table, $id, $select);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array[]
     */
    public static function findAll($sql, $params = null) {
        return static::getEngine()->findAll($sql, $params);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $select
     * @return array[]
     */
    public static function findAllByColumns($table, $columns, $select = null) {
        return static::getEngine()->findAllByColumns($table, $columns, $select);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return DbStatement
     */
    public static function find($sql, $params = null) {
        return static::getEngine()->find($sql, $params);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $select
     * @return DbStatement
     */
    public static function findByColumns($table, $columns, $select = null) {
        return static::getEngine()->findByColumns($table, $columns, $select);
    }

    /**
     * @param string $table
     * @param string|array $where
     * @param array $params
     * @return int
     */
    public static function count($table, $where = null, $params = null) {
        return static::getEngine()->count($table, $where, $params);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param string|array $where
     * @param array $params
     * @return mixed
     */
    public static function min(
        $table, $columnName, $where = null, $params = null
    ) {
        return static::getEngine()->min($table, $columnName, $where, $params);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param string|array $where
     * @param array $params
     * @return mixed
     */
    public static function max(
        $table, $columnName, $where = null, $params = null
    ) {
        return static::getEngine()->max($table, $columnName, $where, $params);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param string|array $where
     * @param array $params
     * @return mixed
     */
    public static function sum(
        $table, $columnName, $where = null, $params = null
    ) {
        return static::getEngine()->sum($table, $columnName, $where, $params);
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param string|array $where
     * @param array $params
     * @return mixed
     */
    public static function average(
        $table, $columnName, $where = null, $params = null
    ) {
        return static::getEngine()->average(
            $table, $columnName, $where, $params
        );
    }

    /**
     * @param string $table
     * @param array $row
     * @return void
     */
    public static function insert($table, $row) {
        static::getEngine()->insert($table, $row);
    }

    /**
     * @param string $table
     * @param array[] $rows
     * @param array $options
     * @return void
     */
    public static function insertAll($table, $rows, $options = []) {
        static::getEngine()->insertAll($table, $rows, $options);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param string|array $where
     * @param array $params
     * @return int
     */
    public static function update($table, $columns, $where, $params = null) {
        return static::getEngine()->update($table, $columns, $where, $params);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param mixed $id
     * @return bool
     */
    public static function updateById($table, $columns, $id) {
        return static::getEngine()->updateById($table, $columns, $id);
    }

    /**
     * @param string $table
     * @param string|array $where
     * @param array $params
     * @return int
     */
    public static function delete($table, $where, $params = null) {
        return static::getEngine()->delete($table, $where, $params);
    }

    /**
     * @param string $table
     * @param mixed $id
     * @return bool
     */
    public static function deleteById($table, $id) {
        return static::getEngine()->deleteById($table, $id);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return int
     */
    public static function execute($sql, $params = null) {
        return static::getEngine()->execute($sql, $params);
    }

    /**
     * @return mixed
     */
    public static function getLastInsertId() {
        return static::getEngine()->getLastInsertId();
    }

    /**
     * @return void
     */
    public static function beginTransaction() {
        static::getEngine()->beginTransaction();
    }

    /**
     * @return void
     */
    public static function commit() {
        static::getEngine()->commit();
    }

    /**
     * @return void
     */
    public static function rollback() {
        static::getEngine()->rollback();
    }

    /**
     * @return bool
     */
    public static function inTransaction() {
        return static::getEngine()->inTransaction();
    }

    /**
     * @param string $identifier
     * @return string
     */
    public static function quoteIdentifier($identifier) {
        return static::getEngine()->quoteIdentifier($identifier);
    }

    /**
     * @param string $sql
     * @param array $driverOptions
     * @return DbStatement 
     */
    public static function prepare($sql, $driverOptions = []) {
        return static::getEngine()->prepare($sql, $driverOptions);
    }

    /**
     * @param bool $shouldConnect
     * @return DbConnection
     */
    public static function getConnection($shouldConnect = true) {
        return static::getEngine()->getConnection($shouldConnect);
    }

    /**
     * @param DbConnection $connection
     * @return void
     */
    public static function setConnection($connection) {
        static::getEngine()->setConnection($connection);
    }

    /**
     * @param string $name
     * @return void
     */
    public static function connect($name = null) {
        static::getEngine()->connect($name);
    }

    /**
     * @param string $name
     * @return void
     */
    public static function removeConnection($name = null) {
        static::getEngine()->removeConnection($name);
    }

    /**
     * @param DbConnection|string $connectionOrConnectionName
     * @param Closure $callback
     * @return mixed
     */
    public static function useConnection(
        $connectionOrConnectionName, $callback
    ) {
        return static::getEngine()->useConnection(
            $connectionOrConnectionName, $callback
        );
    }

    /**
     * @return void
     */
    public static function removeAllConnections() {
        static::getEngine()->removeAllConnections();
    }

    /**
     * @return DbClientEngine
     */
    public static function getEngine() {
        return Registry::get('hyperframework.db.client_engine', function() {
            $class = Config::getClass(
                'hyperframework.db.client_engine_class', DbClientEngine::class
            );
            return new $class;
        });
    }
}
