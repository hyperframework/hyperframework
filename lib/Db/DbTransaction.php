<?php
namespace Hyperframework\Db;

use Exception;
use Throwable;
use Closure;

class DbTransaction {
    private static $connections = [];
    private static $counts = [];

    /**
     * @param Closure $callback
     * @return mixed
     */
    public static function run($callback) {
        $connection = DbClient::getConnection();
        $index = array_search($connection, self::$connections, true);
        if ($index === false) {
            self::$connections[] = $connection;
            self::$counts[] = 0;
            end(self::$connections);
            $index = key(self::$connections);
        }
        $result = null;
        $count = self::$counts[$index];
        ++self::$counts[$index];
        try {
            if ($count === 0) {
                $connection->beginTransaction();
            }
            $e = null;
            try {
                $result = $callback();
                if ($count === 0) {
                    $connection->commit();
                }
            } catch (Exception $e) {} catch (Throwable $e) {}
            if ($e !== null) {
                if ($count === 0) {
                    $connection->rollback();
                }
                throw $e;
            }
        } finally {
            if ($count === 0) {
                unset(self::$counts[$index]);
                unset(self::$connections[$index]);
            } else {
                --self::$counts[$index];
            }
        }
        return $result;
    }
}
