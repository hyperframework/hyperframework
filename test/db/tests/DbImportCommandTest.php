<?php
namespace Hyperframework\Db;

use Hyperframework\Common\Config;
use Hyperframework\Db\Test\TestCase as Base;

class DbImportCommandTest extends Base {
    protected function tearDown() {
        DbClient::delete('Document', null);
        parent::tearDown();
    }

    public function testExecute() {
        DbImportCommand::execute(
            'Document',
            [['id' => 1, 'name' => 'doc 1', 'decimal' => 12.34]]
        );
        $this->assertSame(1, DbClient::count('Document'));
    }

    public function testBatchSizeOption() {
        Config::set('hyperframework.db.operation_profiler.enable', true);
        Config::set('hyperframework.db.operation_profiler.enable_logger', false);
        $mock = $this->getMockBuilder(DbOperationProfiler::class)->setMethods([
            'onPreparedStatementExecuting', 'onPreparedStatementExecuted'
        ])->getMock();
        $mock->expects($this->exactly(2))->method('onPreparedStatementExecuting');
        $mock->expects($this->exactly(2))->method('onPreparedStatementExecuted');
        $mock->run();
        DbImportCommand::execute(
            'Document',
            [[1, 'doc 1', 12.34], [2, 'doc 2', 0]],
            [
                'column_names' => ['id', 'name', 'decimal'],
                'batch_size' => 1
            ]
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidBatchSizeOption() {
        DbImportCommand::execute(
            'Document', [[1, 'doc 1', 12.34]], ['batch_size' => 0]
        );
    }

    public function testColumnNameOption() {
        DbImportCommand::execute(
            'Document',
            [[1, 'doc 1', 12.34]],
            ['column_names' => ['id', 'name', 'decimal']]
        );
        $this->assertSame(1, DbClient::count('Document'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidColumnNameOption() {
        DbImportCommand::execute(
            'Document',
            [[1, 'doc 1', 12.34]],
            ['column_names' => 'name']
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidFirstRowType() {
        DbImportCommand::execute('Document', ['name']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidNonFirstRowType() {
        DbImportCommand::execute(
            'Document',
            [['id' => 1, 'name' => 'doc 1', 'decimal' => 12.34], 'name']
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidColumnNumber() {
        DbImportCommand::execute(
            'Document',
            [['id' => 1, 'name' => 'doc 1', 'decimal' => 12.34], ['id' => 2]]
        );
    }
}
