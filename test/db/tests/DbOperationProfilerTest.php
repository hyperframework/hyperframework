<?php
namespace Hyperframework\Db;

use stdClass;
use Hyperframework\Common\Config;
use Hyperframework\Db\Test\DbOperationProfileHandler;
use Hyperframework\Db\Test\CustomLogger;
use Hyperframework\Db\Test\TestCase as Base;

class DbOperationProfilerTest extends Base {
    private $statement;
    private $connection;
    private $sql = 'SELECT * FROM Document';
    private $profiler;

    protected function setUp() {
        parent::setUp();
        DbClient::connect('backup');
        $this->connection = DbClient::getConnection();
        $this->statement = DbClient::prepare($this->sql);
        Config::set('hyperframework.db.operation_profiler.enable', true);
        Config::set('hyperframework.db.operation_profiler.enable_logger', false);
        $this->profiler = new DbOperationProfiler;
    }

    protected function tearDown() {
        $this->deleteAppLogFile();
        Test\DbOperationProfileHandler::setDelegate(null);
        parent::tearDown();
    }

    public function testTransactionProfile() {
        $this->mockProfileHandler(function(array $profile) {
            return 'backup' === $profile['connection_name']
                && 'begin' === $profile['operation']
                && isset($profile['start_time'])
                && isset($profile['running_time']);
        });
        $this->profiler->onTransactionOperationExecuting(
            $this->connection, 'begin'
        );
        $this->profiler->onTransactionOperationExecuted('success');
    }

    public function testSqlStatementExecutionProfile() {
        $this->mockProfileHandler(
            function(array $profile) {
                return 'backup' === $profile['connection_name']
                    && $this->sql === $profile['sql']
                    && isset($profile['start_time'])
                    && isset($profile['running_time']);
            }
        );
        $this->profiler->onSqlStatementExecuting(
            $this->connection, $this->sql
        );
        $this->profiler->onSqlStatementExecuted('success');
    }

    public function testPreparedStatementExecutionProfile() {
        $this->mockProfileHandler(
            function(array $profile) {
                return 'backup' === $profile['connection_name']
                    && $this->sql === $profile['sql']
                    && isset($profile['start_time'])
                    && isset($profile['running_time']);
            }
        );
        $this->profiler->onPreparedStatementExecuting(
            $this->statement, null
        );
        $this->profiler->onPreparedStatementExecuted('success');
    }

    public function testLogProfile() {
        Config::set(
            'hyperframework.db.operation_profiler.enable_logger', true
        );
        Config::set(
            'hyperframework.logging.level', 'DEBUG'
        );
        $this->profiler->onSqlStatementExecuting(
            $this->connection, $this->sql
        );
        $this->profiler->onSqlStatementExecuted('success');
        $this->assertTrue(file_exists(
            Config::getAppRootPath() . '/log/app.log'
        ));
    }

    private function mockProfileHandler($handleCallback) {
        Config::set(
            'hyperframework.db.operation_profiler.profile_handler_class',
            'Hyperframework\Db\Test\DbOperationProfileHandler'
        );
        $mock = $this->getMock('Hyperframework\Db\Test\DbOperationProfileHandler');
        Test\DbOperationProfileHandler::setDelegate($mock);
        return $mock->expects($this->once())->method('handle')
            ->will($this->returnCallback($handleCallback));
    }
}
