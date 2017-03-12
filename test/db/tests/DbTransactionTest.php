<?php
namespace Hyperframework\Db;

use Exception;
use Hyperframework\Db\Test\TestCase as Base;

class DbTransactionTest extends Base {
    public function testRun() {
        $this->assertFalse(DbClient::inTransaction());
        DbTransaction::run(function() {
            $this->assertTrue(DbClient::inTransaction());
        });
        $this->assertFalse(DbClient::inTransaction());
    }

    public function testRollbackAutomaticly() {
        try {
            DbTransaction::run(function() {
                throw new Exception;
            });
        } catch (Exception $e) {
            $this->assertFalse(DbClient::inTransaction());
        }
    }

    public function testNestedTransactionUsingSameConnection() {
        DbTransaction::run(function() {
            DbTransaction::run(function() {
                $this->assertTrue(DbClient::inTransaction());
            });
            $this->assertTrue(DbClient::inTransaction());
        });
        $this->assertFalse(DbClient::inTransaction());
    }

    public function testNestedTransactionUsingDifferentConnections() {
        DbTransaction::run(function() {
            DbClient::connect('backup');
            DbTransaction::run(function() {
                $this->assertTrue(DbClient::inTransaction());
            });
            $this->assertFalse(DbClient::inTransaction());
            DbClient::connect();
            $this->assertTrue(DbClient::inTransaction());
        });
        $this->assertFalse(DbClient::inTransaction());
    }
}
