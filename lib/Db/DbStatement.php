<?php
namespace Hyperframework\Db;

use PDO;
use Exception;
use Throwable;
use PDOStatement;
use Hyperframework\Common\EventEmitter;

class DbStatement {
    private $pdoStatement;
    private $connection;
    private $params = [];

    /**
     * @param PDOStatement $pdoStatement
     * @param DbConnection $connection
     * @return void
     */
    public function __construct($pdoStatement, $connection) {
        $this->pdoStatement = $pdoStatement;
        $this->connection = $connection;
    }

    /**
     * @param array $params
     * @return void
     */
    public function execute($params = null) {
        if ($params !== null) {
            $this->params = $params;
        }
        EventEmitter::emit(
            'hyperframework.db.prepared_statement_executing',
            [$this, $this->params]
        );
        $e = null;
        try {
            $this->pdoStatement->execute($params);
        } catch (Exception $e) {
            throw $e;
        } catch (Throwable $e) {
            throw $e;
        } finally {
            EventEmitter::emit(
                'hyperframework.db.prepared_statement_executed',
                [$e === null ? 'success' : 'failure']
            );
        }
    }

    /**
     * @return DbConnection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getSql() {
        return $this->pdoStatement->queryString;
    }

    /**
     * @param mixed $column
     * @param mixed &$param
     * @param int $type
     * @param int $maxLength
     * @param array $driverOptions
     * @return void
     */
    public function bindColumn(
        $column,
        &$param,
        $type = PDO::PARAM_STR,
        $maxLength = null,
        $driverOptions = null
    ) {
        $this->pdoStatement->bindColumn(
            $column, $param, $type, $maxLength, $driverOptions
        );
    }

    /**
     * @param mixed $param
     * @param mixed &$variable
     * @param int $dataType
     * @param int $length
     * @param array $driverOptions
     * @return void
     */
    public function bindParam(
        $param,
        &$variable,
        $dataType = PDO::PARAM_STR,
        $length = null,
        $driverOptions = null
    ) {
        $this->pdoStatement->bindParam(
            $param, $variable, $dataType, $length, $driverOptions
        );
        $this->params[$param] = $variable;
    }

    /**
     * @param mixed $param
     * @param mixed $value
     * @param int $dataType
     * @return void
     */
    public function bindValue($param, $value, $dataType = PDO::PARAM_STR) {
        $this->pdoStatement->bindValue($param, $value, $dataType);
        $this->params[$param] = $value;
    }

    /**
     * @return void
     */
    public function closeCursor() {
        $this->pdoStatement->closeCursor();
    }

    /**
     * @return int
     */
    public function columnCount() {
        return $this->pdoStatement->columnCount();
    }

    /**
     * @return void
     */
    public function debugDumpParams() {
        $this->pdoStatement->debugDumpParams();
    }

    /**
     * @return string
     */
    public function errorCode() {
        return $this->pdoStatement->errorCode();
    }

    /**
     * @return array
     */
    public function errorInfo() {
        return $this->pdoStatement->errorInfo();
    }

    /**
     * @param int $fetchStyle
     * @param int $cursorOrientation
     * @param int $cursorOffset
     * @return mixed
     */
    public function fetch(
        $fetchStyle = null,
        $cursorOrientation = PDO::FETCH_ORI_NEXT,
        $cursorOffset = 0
    ) {
        return $this->pdoStatement->fetch(
            $fetchStyle, $cursorOrientation, $cursorOffset
        );
    }

    /**
     * @param int $fetchStyle
     * @param int $fetchArgument
     * @param array $constructorArguments
     * @return array
     */
    public function fetchAll(
        $fetchStyle = null,
        $fetchArgument = null,
        $constructorArguments = []
    ) {
        switch (func_num_args()) {
            case 0: return $this->pdoStatement->fetchAll();
            case 1: return $this->pdoStatement->fetchAll($fetchStyle);
            case 2: return $this->pdoStatement->fetchAll(
                $fetchStyle, $fetchArgument
            );
            default: return $this->pdoStatement->fetchAll(
                $fetchStyle, $fetchArgument, $constructorArguments
            );
        }
    }

    /**
     * @param int $columnNumber
     * @return mixed
     */
    public function fetchColumn($columnNumber = 0) {
        return $this->pdoStatement->fetchColumn($columnNumber);
    }

    /**
     * @param string $className
     * @param array $constructorArguments
     * @return object
     */
    public function fetchObject(
        $className = "stdClass", $constructorArguments = []
    ) {
        return $this->pdoStatement->fetchObject(
            $className, $constructorArguments
        );
    }

    /**
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute($attribute) {
        return $this->pdoStatement->getAttribute($attribute);
    }

    /**
     * @param int $column
     * @return array
     */
    public function getColumnMeta($column) {
        return $this->pdoStatement->getColumnMeta($column);
    }

    /**
     * @return void
     */
    public function nextRowset() {
        return $this->pdoStatement->nextRowset();
    }

    /**
     * @return int
     */
    public function rowCount() {
        return $this->pdoStatement->rowCount();
    }

    /**
     * @param int $attribute
     * @param mixed $value
     */
    public function setAttribute($attribute, $value) {
        $this->pdoStatement->setAttribute($attribute, $value);
    }

    /**
     * @param int $mode
     * @param mixed $extraParam1
     * @param array $extraParam2
     * @return void
     */
    public function setFetchMode(
        $mode, $extraParam1 = null, $extraParam2 = null
    ) {
        call_user_func_array(
            [$this->pdoStatement, 'setFetchMode'], func_get_args()
        );
    }
}
