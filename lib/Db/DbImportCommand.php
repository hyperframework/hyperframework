<?php
namespace Hyperframework\Db;

use PDO;
use InvalidArgumentException;

class DbImportCommand {
    /**
     * @param string $table
     * @param array[] $rows
     * @param array $options
     * @return void
     */
    public static function execute($table, $rows, $options = []) {
        $count = count($rows);
        if ($count === 0) {
            return;
        }
        $columnNames = null;
        if (isset($options['column_names'])) {
            $columnNames = $options['column_names'];
            if (is_array($columnNames) === false) {
                throw new InvalidArgumentException(
                    "The value of option 'column_names' must be an array, "
                        . gettype($columnNames) . ' given.'
                );
            }
        } else {
            if (is_array($rows[0]) === false) {
                throw new InvalidArgumentException(
                    "Row must be an array, "
                        . gettype($rows[0]) . " given at row 0."
                );
            }
            $columnNames = array_keys($rows[0]);
        }
        $columnCount = count($columnNames);
        if ($columnCount === 0) {
            return;
        }
        if (isset($options['batch_size'])) {
            $batchSize = (int)$options['batch_size'];
            if ($batchSize <= 0) {
                throw new InvalidArgumentException(
                    "The value of option 'batch_size' must be greater than 0, "
                        . $batchSize . ' given.'
                );
            }
        } else {
            $batchSize = 1000;
        }
        foreach ($columnNames as &$columnName) {
            $columnName = DbClient::quoteIdentifier($columnName);
        }
        $prefix = 'INSERT INTO ' . DbClient::quoteIdentifier($table)
            . '(' . implode($columnNames, ', ') . ') VALUES';
        $placeHolders = '(' . str_repeat('?, ', $columnCount - 1) . '?)';
        $statement = null;
        $index = 0;
        while ($index < $count) {
            $values = [];
            $size = $batchSize;
            if ($index + $batchSize >= $count) {
                $size = $count - $index;
            }
            if ($statement === null || $size !== $batchSize) {
                $sql = $prefix . str_repeat($placeHolders . ',', $size - 1)
                    . $placeHolders;
                $statement = DbClient::prepare(
                    $sql, [PDO::ATTR_EMULATE_PREPARES => false]
                );
            }
            while ($size > 0) {
                if (is_array($rows[$index]) === false) {
                    throw new InvalidArgumentException(
                        "Row must be an array, "
                            . gettype($rows[0]) . " given at row $index."
                    );
                }
                if (count($rows[$index]) !== $columnCount) {
                    throw new InvalidArgumentException(
                        "Number of columns is invalid at row $index,"
                            . " expected $columnCount, actual "
                            . count($rows[$index]) . "."
                    );
                }
                $values = array_merge($values, array_values($rows[$index]));
                ++$index;
                --$size;
            }
            $statement->execute($values);
        }
    }
}
