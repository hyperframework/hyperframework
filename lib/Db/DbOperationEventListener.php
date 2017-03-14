<?php
namespace Hyperframework\Db;

use Hyperframework\Common\EventEmitter;

abstract class DbOperationEventListener {
    /**
     * @return array
     */
    public function getEventBindings() {
        return [
            [
                'name' => 'hyperframework.db.transaction_operation_executing',
                'callback' => [$this, 'onTransactionOperationExecuting']
            ], [
                'name' => 'hyperframework.db.transaction_operation_executed',
                'callback' => [$this, 'onTransactionOperationExecuted']
            ], [
                'name' => 'hyperframework.db.sql_statement_executing',
                'callback' => [$this, 'onSqlStatementExecuting']
            ], [
                'name' => 'hyperframework.db.sql_statement_executed',
                'callback' => [$this, 'onSqlStatementExecuted']
            ], [
                'name' => 'hyperframework.db.prepared_statement_executing',
                'callback' => [$this, 'onPreparedStatementExecuting']
            ], [
                'name' => 'hyperframework.db.prepared_statement_executed',
                'callback' => [$this, 'onPreparedStatementExecuted']
            ]
        ];
    }

    /**
     * @param DbConnection $connection
     * @param string $operation
     * @return void
     */
    public function onTransactionOperationExecuting($connection, $operation) {
    }

    /**
     * @param string $status
     * @return void
     */
    public function onTransactionOperationExecuted($status) {
    }

    /**
     * @param DbConnection $connection
     * @param string $sql
     * @return void
     */
    public function onSqlStatementExecuting($connection, $sql) {
    }

    /**
     * @param string $status
     * @return void
     */
    public function onSqlStatementExecuted($status) {
    }

    /**
     * @param DbStatement $statement
     * @param array $params
     * @return void
     */
    public function onPreparedStatementExecuting($statement, $params) {
    }

    /**
     * @param string $status
     * @return void
     */
    public function onPreparedStatementExecuted($status) {
    }
}
