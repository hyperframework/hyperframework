<?php
namespace Hyperframework\Db;

use PDO;
use Hyperframework\Db\Test\TestCase as Base;

class DbConnectionFactoryTest extends Base {
    private $factory;

    protected function setUp() {
        parent::setUp();
        $this->factory = new DbConnectionFactory;
    }

    public function testCreateDefaultConnection() {
        $connection = $this->factory->createConnection();
        $this->assertSame(
            0, $connection->getAttribute(PDO::ATTR_EMULATE_PREPARES)
        );
    }

    public function testCreateNamedConnection() {
        $connection = $this->factory->createConnection('backup');
        $this->assertSame('backup', $connection->getName());
        $this->assertSame(
            1, $connection->getAttribute(PDO::ATTR_EMULATE_PREPARES)
        );
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testCreateNonExistentConnection() {
        $this->factory->createConnection('unknown');
    }

    /**
     * @expectedException Hyperframework\Common\ConfigException
     */
    public function testCreateConnectionWithoutDsnConfig() {
        $this->factory->createConnection('invalid');
    }
}
