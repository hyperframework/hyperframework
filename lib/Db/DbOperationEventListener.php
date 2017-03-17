<?php
namespace Hyperframework\Db;

abstract class DbOperationEventListener {
    /**
     * @return array
     */
    public function getEventBindings() {
        return [
            'hyperframework.db.transaction_operation_executing'
                => 'onTransactionOperationExecuting',
            'hyperframework.db.transaction_operation_executed'
                => 'onTransactionOperationExecuted',
            'hyperframework.db.sql_statement_executing'
                => 'onSqlStatementExecuting',
            'hyperframework.db.sql_statement_executed'
                => 'onSqlStatementExecuted',
            'hyperframework.db.prepared_statement_executing'
                => 'onPreparedStatementExecuting',
            'hyperframework.db.prepared_statement_executed'
                => 'onPreparedStatementExecuted'
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
