<?php
namespace Hyperframework\Db;

use Exception;
use stdClass;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Db\Test\DbCustomClientEngine;
use Hyperframework\Db\DbClientEngine;
use Hyperframework\Db\Test\TestCase as Base;

class DbClientTest extends Base {
    public function testFindColumn() {
        $this->mockEngineMethod('findColumn')->with(
            $this->equalTo('sql'), $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::findColumn('sql', ['param']));
    }

    public function testFindColumnWithoutParam() {
        $this->mockEngineMethod('findColumn')->with(
            $this->equalTo('sql')
        );
        DbClient::findColumn('sql');
    }

    public function testFindColumnWithMultipleParams() {
        $this->mockEngineMethod('findColumn')->with(
            $this->equalTo('sql'),
            $this->equalTo(['paramA', 'paramB'])
        );
        DbClient::findColumn('sql', ['paramA', 'paramB']);
    }

    public function testFindColumnWithArray() {
        $this->mockEngineMethod('findColumn')->with(
            $this->equalTo('sql'),
            $this->equalTo(['param'])
        );
        DbClient::findColumn('sql', ['param']);
    }

    public function testFindColumnByColumns() {
        $this->mockEngineMethod('findColumnByColumns')->with(
            $this->equalTo('table'),
            $this->equalTo('id'),
            $this->equalTo([])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::findColumnByColumns('table', 'id', []));
    }

    public function testFindColumnById() {
        $this->mockEngineMethod('findColumnById')->with(
            $this->equalTo('table'),
            $this->equalTo('name'),
            $this->equalTo(1)
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::findColumnById('table', 'name', 1));
    }

    public function testFindRow() {
        $this->mockEngineMethod('findRow')->with(
            $this->equalTo('sql'), $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::findRow('sql', ['param']));
    }

    public function testFindRowByColumns() {
        $this->mockEngineMethod('findRowByColumns')->with(
            $this->equalTo('table'),
            $this->equalTo([]),
            $this->equalTo(['name'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::findRowByColumns('table', [], ['name']));
    }

    public function testFindAll() {
        $this->mockEngineMethod('findAll')->with(
            $this->equalTo('sql'), $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::findAll('sql', ['param']));
    }

    public function testFindAllByColumns() {
        $this->mockEngineMethod('findAllByColumns')->with(
            $this->equalTo('table'),
            $this->equalTo([]),
            $this->equalTo(['name'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::findAllByColumns('table', [], ['name']));
    }

    public function testFind() {
        $this->mockEngineMethod('find')->with(
            $this->equalTo('sql'), $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::find('sql', ['param']));
    }

    public function testFindByColumns() {
        $this->mockEngineMethod('findByColumns')->with(
            $this->equalTo('table'),
            $this->equalTo([]),
            $this->equalTo(['name'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::findByColumns('table', [], ['name']));
    }

    public function testCount() {
        $this->mockEngineMethod('count')->with(
            $this->equalTo('table'),
            $this->equalTo('where'),
            $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::count("table", 'where', ['param']));
    }

    public function testMin() {
        $this->mockEngineMethod('min')->with(
            $this->equalTo('table'),
            $this->equalTo('column'),
            $this->equalTo('where'),
            $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::min("table", 'column', 'where', ['param']));
    }

    public function testMax() {
        $this->mockEngineMethod('max')->with(
            $this->equalTo('table'),
            $this->equalTo('column'),
            $this->equalTo('where'),
            $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::max("table", 'column', 'where', ['param']));
    }

    public function testAverage() {
        $this->mockEngineMethod('average')->with(
            $this->equalTo('table'),
            $this->equalTo('column'),
            $this->equalTo('where'),
            $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(
            DbClient::average("table", 'column', 'where', ['param'])
        );
    }

    public function testInsert() {
        $this->mockEngineMethod('insert')->with(
            $this->equalTo('table'),
            $this->equalTo(['key' => 'value'])
        );
        DbClient::insert('table', ['key' => 'value']);
    }

    public function testUpdate() {
        $this->mockEngineMethod('update')->with(
            $this->equalTo('table'),
            $this->equalTo([]),
            $this->equalTo('where'),
            $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::update('table', [], 'where', ['param']));
    }

    public function testUpdateById() {
        $this->mockEngineMethod('updateById')->with(
            $this->equalTo('table'),
            $this->equalTo([]),
            $this->equalTo('id')
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::updateById('table', [], 'id'));
    }

    public function testDelete() {
        $this->mockEngineMethod('delete')->with(
            $this->equalTo('table'),
            $this->equalTo('where'),
            $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::delete('table', 'where', ['param']));
    }

    public function testDeleteById() {
        $this->mockEngineMethod('deleteById')->with(
            $this->equalTo('table'),
            $this->equalTo('id')
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::deleteById('table', 'id'));
    }

    public function testExecute() {
        $this->mockEngineMethod('execute')->with(
            $this->equalTo('sql'), $this->equalTo(['param'])
        )->will($this->returnValue(true));
        $this->assertTrue(DbClient::execute('sql', ['param']));
    }

    public function testGetLastInsertId() {
        $this->mockEngineMethod('getLastInsertId')->will(
            $this->returnValue(true)
        );
        $this->assertTrue(DbClient::getLastInsertId());
    }

    public function testBeginTransaction() {
        $this->mockEngineMethod('beginTransaction');
        DbClient::beginTransaction();
    }

    public function testInTransaction() {
        $this->mockEngineMethod('inTransaction')->will(
            $this->returnValue(true)
        );
        $this->assertTrue(DbClient::inTransaction());
    }

    public function testCommit() {
        $this->mockEngineMethod('commit');
        DbClient::commit();
    }

    public function testRollback() {
        $this->mockEngineMethod('rollback');
        DbClient::rollback();
    }

    public function testQuoteIdentifier() {
        $this->mockEngineMethod('quoteIdentifier')->with(
            $this->equalTo('string')
        )->will($this->returnValue($this));
        $this->assertSame($this, DbClient::quoteIdentifier('string'));
    }

    public function testPrepare() {
        $this->mockEngineMethod('prepare')->with(
            $this->equalTo('sql'), $this->equalTo([])
        )->will($this->returnValue($this));
        $this->assertSame($this, DbClient::prepare('sql', []));
    }

    public function testSetConnection() {
        $connection = DbClient::getConnection(true);
        $this->mockEngineMethod('setConnection')->with(
            $this->equalTo($connection)
        );
        DbClient::setConnection($connection);
    }

    public function testGetConnection() {
        $this->mockEngineMethod('getConnection')->with(
            $this->equalTo(true)
        )->will(
            $this->returnValue(true)
        );
        $this->assertTrue(DbClient::getConnection());
    }

    public function testRemoveConnection() {
        $this->mockEngineMethod('removeConnection')->with(
            $this->equalTo(null)
        );
        DbClient::removeConnection();
    }

    public function testConnect() {
        $this->mockEngineMethod('connect')->with($this->equalTo('master'));
        DbClient::connect('master');
    }

    public function testGetDefaultEngine() {
        $this->assertTrue(DbClient::getEngine() instanceof DbClientEngine);
    }

    public function testSetEngineUsingConfig() {
        Config::set(
            'hyperframework.db.client_engine_class',
            'Hyperframework\Db\Test\DbCustomClientEngine'
        );
        $this->assertTrue(
            DbClient::getEngine() instanceof DbCustomClientEngine
        );
    }

    /**
     * @expectedException Hyperframework\Common\ClassNotFoundException
     */
    public function testInvalidEngineConfig() {
        Config::set('hyperframework.db.client_engine_class', 'Unknown');
        DbClient::getEngine();
    }

    private function mockEngineMethod($method) {
        $engine = $this->getMock('Hyperframework\Db\DbClientEngine');
        Registry::set('hyperframework.db.client_engine', $engine);
        return $engine->expects($this->once())->method($method);
    }
}
