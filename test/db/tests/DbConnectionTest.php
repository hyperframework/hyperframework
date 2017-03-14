<?php
namespace Hyperframework\Db;

use PDO;
use Hyperframework\Common\Config;
use Hyperframework\Common\EventEmitter;
use Hyperframework\Db\Test\TestCase as Base;

class DbConnectionTest extends Base {
    private $connection;

    protected function setUp() {
        parent::setUp();
        $factory = new DbConnectionFactory;
        $this->connection = $factory->createConnection();
    }

    public function testGetName() {
        $this->assertSame(null, $this->connection->getName());
    }

    public function testPrepare() {
        $this->assertTrue(
            $this->connection->prepare('SELECT * FROM Document')
                instanceof DbStatement
        );
    }

    public function testExec() {
        $this->assertSame(0, $this->connection->exec('DELETE FROM Document'));
    }

    public function testQuery() {
        $this->assertTrue(
            $this->connection->query('SELECT * FROM Document')
                instanceof DbStatement
        );
    }

    public function testBeginTransaction() {
        $this->mockProfileHandler();
        $this->connection->beginTransaction();
        $this->assertTrue($this->connection->inTransaction());
    }

    public function testRollback() {
        $this->connection->beginTransaction();
        $this->mockProfileHandler();
        $this->connection->rollback();
        $this->assertFalse($this->connection->inTransaction());
    }

    public function testCommit() {
        $this->connection->beginTransaction();
        $this->mockProfileHandler();
        $this->connection->commit();
        $this->assertFalse($this->connection->inTransaction());
    }

    private function mockProfileHandler() {
        Config::set('hyperframework.db.operation_profiler.enable', true);
        Config::set('hyperframework.db.operation_profiler.enable_logger', false);
        $mock = $this->getMockBuilder(DbOperationProfiler::class)->setMethods([
            'onTransactionOperationExecuting', 'onTransactionOperationExecuted'
        ])->getMock();
        $mock->expects($this->once())->method('onTransactionOperationExecuting');
        $mock->expects($this->once())->method('onTransactionOperationExecuted');
        EventEmitter::addListener($mock);
    }

    public function testQuoteIdentifier() {
        $result = $this->connection->quoteIdentifier('x');
        $this->assertSame(3, strlen($result));
        $this->assertSame('x', $result[1]);
        $this->assertSame($result[0], $result[2]);
    }

    public function testGetIdentifierQuotationMarks() {
        $this->assertSame(2, count($this->callProtectedMethod(
            $this->connection, 'getIdentifierQuotationMarks'
        )));
    }
}
