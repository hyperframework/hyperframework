<?php
namespace Hyperframework\Db;

use PDO;
use Exception;
use Hyperframework\Db\Test\DbCustomConnection;
use Hyperframework\Common\Config;
use Hyperframework\Db\Test\TestCase as Base;

class DbClientEngineTest extends Base {
    private $engine;

    protected function setUp() {
        parent::setUp();
        DbImportCommand::execute(
            'Document',
            [[1, 'doc 1', 12.34], [2, 'doc 2', null]],
            ['column_names' => ['id', 'name', 'decimal']]
        );
        $this->engine = new DbClientEngine;
    }

    protected function tearDown() {
        DbClient::delete('Document', null);
        parent::tearDown();
    }

    public function testFindColumnByColumns() {
        $this->assertSame(1, $this->engine->findColumnByColumns(
            'Document', 'id', ['name' => 'doc 1']
        ));
    }

    public function testFindColumnById() {
        $this->assertSame(
            'doc 1',
            $this->engine->findColumnById('Document', 'name', 1)
        );
    }

    public function testFindRow() {
        $this->assertSame(
            ['name' => 'doc 1'],
            $this->engine->findRow("SELECT name FROM Document WHERE id = 1")
        );
    }

    public function testFindRowByColumns() {
        $this->assertSame(
            ['id' => 1],
            $this->engine->findRowByColumns(
                'Document', ['name' => 'doc 1'], ['id']
            )
        );
    }

    public function testFindRowById() {
        $this->assertSame(
            ['id' => 1, 'name' => 'doc 1', 'decimal' => '12.34'],
            $this->engine->findRowById('Document', '1')
        );
    }

    public function testFindAll() {
        $this->assertEquals(
            [['name' => 'doc 1']],
            $this->engine->findAll("SELECT name FROM Document WHERE id = 1")
        );
    }

    public function testFindAllByColumns() {
        $this->assertSame(
            [['id' => 1]],
            $this->engine->findAllByColumns(
                'Document', ['name' => 'doc 1'], ['id']
            )
        );
    }

    public function testFind() {
        $statement = $this->engine->find("SELECT * FROM Document");
        $this->assertTrue($statement instanceof DbStatement);
    }

    public function testFindByColumns() {
        $statement = $this->engine->findByColumns(
            'Document', ['name' => 'doc 1']
        );
        $this->assertSame(1, $statement->rowCount());
        $this->assertSame(3, count($statement->fetch(PDO::FETCH_ASSOC)));
        $statement = $this->engine->findByColumns(
            'Document', ['name' => 'doc 1', 'decimal' => 12.34]
        );
        $this->assertSame(1, $statement->rowCount());
        $this->assertSame(
            2, $this->engine->findByColumns('Document', [])->rowCount()
        );
        $this->assertSame(1, count($this->engine->findByColumns(
            'Document', [], ['name']
        )->fetch(PDO::FETCH_ASSOC)));
        $this->assertSame(2, count($this->engine->findByColumns(
            'Document', [], ['id', 'name']
        )->fetch(PDO::FETCH_ASSOC)));
        $this->assertSame(3, count($this->engine->findByColumns(
            'Document', [], []
        )->fetch(PDO::FETCH_ASSOC)));
    }

    public function testCount() {
        $this->assertSame(1, $this->engine->count('Document', 'id > ?', [1]));
        $this->assertSame(1, $this->engine->count(
            'Document', ['name' => 'doc 1'])
        );
    }

    public function testMin() {
        $this->assertSame(
            1, $this->engine->min('Document', 'id', 'id > ?', [0])
        );
        $this->assertSame(
            1, $this->engine->min('Document', 'id', ['name' => 'doc 1'])
        );
    }

    public function testMax() {
        $this->assertSame(
            2, $this->engine->max('Document', 'id', 'id > ?', [1])
        );
        $this->assertSame(
            1, $this->engine->min('Document', 'id', ['name' => 'doc 1'])
        );
    }

    public function testAverage() {
        $this->assertEquals(
            1.5, $this->engine->average('Document', 'id', 'id > ?', [0])
        );
        $this->assertEquals(
            1, $this->engine->average('Document', 'id', ['name' => 'doc 1'])
        );
    }

    public function testInsert() {
        $this->engine->insert('Document', ['id' => 3]);
        $this->assertSame(3, $this->engine->count('Document'));
    }

    public function testUpdate() {
        $this->engine->update(
            'Document', ['name' => 'updated 1'], 'id = ?', [1]
        );
        $row = $this->engine->findRowById('Document', 1);
        $this->assertSame('updated 1', $row['name']);
        $this->engine->update(
            'Document', ['name' => 'updated 2', 'decimal' => '1'], 'id = ?', [1]
        );
        $row = $this->engine->findRowById('Document', 1);
        $this->assertSame('updated 2', $row['name']);
        $this->assertSame('1.00', $row['decimal']);
        $this->engine->update('Document', ['name' => 'updated 3'], ['id' => 1]);
        $row = $this->engine->findRowById('Document', 1);
        $this->assertSame('updated 3', $row['name']);
        $this->engine->update('Document', ['name' => 'updated 4'], null);
        $row = $this->engine->findRowById('Document', 1);
        $this->assertSame('updated 4', $row['name']);
        $row = $this->engine->findRowById('Document', 2);
        $this->assertSame('updated 4', $row['name']);
    }

    public function testDelete() {
        $this->engine->delete('Document', 'id = ?', [1]);
        $this->assertNull($this->engine->findRowById('Document', 1));
        $this->engine->delete('Document', ['id' => 2]);
        $this->assertNull($this->engine->findRowById('Document', 2));
    }

    public function testDeleteAll() {
        $this->engine->delete('Document', null);
        $this->assertTrue(0 === $this->engine->count('Document'));
    }

    public function testDeleteById() {
        $this->engine->delete('Document', 1);
        $this->assertNull($this->engine->findRowById('Document', 1));
    }

    public function testUpdateById() {
        $row = ['name' => 'updated'];
        $this->assertTrue($this->engine->updateById('Document', $row, 1));
        $this->assertSame(
            'updated', $this->engine->findColumnById('Document', 'name', 1)
        );
    }

    public function testExecute() {
        $this->assertSame(
            1, $this->engine->execute('DELETE FROM Document WHERE id = ?', [1])
        );
        $this->assertNull($this->engine->findRowById('Document', 1));
    }

    public function testGetLastInsertId() {
        $row = [];
        $this->engine->insert('Document', $row);
        $this->assertNotNull($this->engine->getLastInsertId());
    }

    public function testTransaction() {
        $this->engine->beginTransaction();
        $this->assertTrue($this->engine->inTransaction());
        $this->engine->delete('Document', 1);
        $this->engine->commit();
        $this->assertFalse($this->engine->inTransaction());
        $this->assertNull($this->engine->findRowById('Document', 1));
    }

    public function testRollback() {
        $this->engine->beginTransaction();
        $this->engine->delete('Document', 1);
        $this->engine->rollback();
        $this->assertTrue(is_array($this->engine->findRowById('Document', 1)));
    }

    public function testQuoteIdentifier() {
       $this->assertSame(
           1, strpos($this->engine->quoteIdentifier('id'), 'id')
       );
    }

    public function testPrepare() {
        $this->assertTrue($this->engine->prepare('SELECT * FROM Document', [])
            instanceof DbStatement);
    }

    public function testUseConnection() {
        $this->assertSame(
            null, $this->engine->getConnection()->getName()
        );
        $this->engine->useConnection('custom', function() {
            $this->assertSame(
                'custom', $this->engine->getConnection()->getName()
            );
            $this->engine->useConnection('backup', function() {
                $this->assertSame(
                    'backup', $this->engine->getConnection()->getName()
                );
            });
            $this->assertSame(
                'custom', $this->engine->getConnection()->getName()
            );
        });
        $this->assertSame(
            null, $this->engine->getConnection()->getName()
        );
    }

    public function testRemoveConnectionWhenConnectionPoolIsEnabled() {
        $connection = new DbCustomConnection;
        $this->engine->setConnection($connection);
        $this->engine->removeConnection();
        $this->assertNull($this->engine->getConnection(false));
        $this->engine->connect('custom');
        $this->assertNotSame($connection, $this->engine->getConnection());
    }

    public function testRemoveConnectionByNameWhenConnectionPoolIsEnabled() {
        $connection = new DbCustomConnection;
        $this->engine->setConnection($connection);
        $this->engine->removeConnection('custom');
        $this->assertNull($this->engine->getConnection(false));
        $this->engine->connect('custom');
        $this->assertNotSame($connection, $this->engine->getConnection());
    }

    public function
        testRemoveNonExistentConnectionByNameWhenConnectionPoolIsEnabled()
    {
        $connection = new DbCustomConnection;
        $this->engine->setConnection($connection);
        $this->engine->removeConnection('unknown');
        $this->assertSame($connection, $this->engine->getConnection());
    }

    public function testRemovePooledConnection() {
        $connection = new DbCustomConnection;
        $this->engine->setConnection($connection);
        $this->engine->connect();
        $this->engine->removeConnection('custom');
        $this->assertNotNull($this->engine->getConnection(false));
        $this->engine->connect('custom');
        $this->assertNotSame($connection, $this->engine->getConnection());
    }

    public function testRemoveNullConnection() {
        $this->engine->removeConnection();
        $this->assertNull($this->engine->getConnection(false));
    }

    public function testGetNullConnection() {
        $this->assertNull($this->engine->getConnection(false));
    }

    public function testConnectWhenConnectionPoolIsEnabled() {
        $this->engine->connect('backup');
        $connection = $this->engine->getConnection();
        $this->engine->connect('backup');
        $this->assertSame('backup', $connection->getName());
        $this->assertSame($this->engine->getConnection(), $connection);
    }
}
