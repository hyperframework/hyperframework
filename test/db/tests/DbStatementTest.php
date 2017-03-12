<?php
namespace Hyperframework\Db;

use Hyperframework\Common\Config;
use Hyperframework\Db\Test\TestCase as Base;

class DbStatementTest extends Base {
    public function testExecute() {
        $statement = DbClient::prepare('SELECT * FROM Document where id != ?');
        $this->mockProfileHandler();
        $statement->bindValue(1, 1);
        $statement->execute();
    }

    public function testGetSql() {
        $sql = 'SELECT * FROM Document';
        $statement = DbClient::prepare($sql);
        $this->assertSame($sql, $statement->getSql());
    }

    public function testGetConnection() {
        $statement = DbClient::prepare('SELECT * FROM Document');
        $this->assertTrue($statement->getConnection() instanceof DbConnection);
    }

    private function mockProfileHandler() {
        Config::set('hyperframework.db.operation_profiler.enable', true);
        Config::set('hyperframework.db.operation_profiler.enable_logger', false);
        $mock = $this->getMockBuilder(DbOperationProfiler::class)->setMethods([
            'onPreparedStatementExecuting', 'onPreparedStatementExecuted'
        ])->getMock();
        $mock->expects($this->once())->method('onPreparedStatementExecuting');
        $mock->expects($this->once())->method('onPreparedStatementExecuted');
        $mock->run();
    }
}
