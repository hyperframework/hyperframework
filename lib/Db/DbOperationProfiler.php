<?php
namespace Hyperframework\Db;

use DateTime;
use DateTimeZone;
use Hyperframework\Common\Config;
use Hyperframework\Common\ConfigException;

class DbOperationProfiler extends DbOperationEventListener {
    private $profile;
    private $profileHandler;
    private $isProfileHandlerInitialized = false;

    /**
     * @return bool
     */
    public function isEnabled() {
        return Config::getBool(
            'hyperframework.db.operation_profiler.enable', false
        );
    }

    /**
     * @param DbConnection $connection
     * @param string $operation
     * @return void
     */
    public function onTransactionOperationExecuting($connection, $operation) {
        if ($this->isEnabled()) {
            $this->initializeProfile(
                $connection, [
                    'type' => 'transaction_operation',
                    'operation' => $operation
                ]
            );
        }
    }

    /**
     * @param string $status
     * @return void
     */
    public function onTransactionOperationExecuted($status) {
        if ($this->isEnabled()) {
            $this->handleProfile($status);
        }
    }

    /**
     * @param DbConnection $connection
     * @param string $sql
     * @return void
     */
    public function onSqlStatementExecuting($connection, $sql) {
        if ($this->isEnabled()) {
            $this->initializeProfile($connection, [
                'type' => 'sql_statement', 'sql' => $sql
            ]);
        }
    }

    /**
     * @param string $status
     * @return void
     */
    public function onSqlStatementExecuted($status) {
        if ($this->isEnabled()) {
            $this->handleProfile($status);
        }
    }

    /**
     * @param DbStatement $statement
     * @param array $params
     * @return void
     */
    public function onPreparedStatementExecuting($statement, $params) {
        if ($this->isEnabled()) {
            $this->initializeProfile(
                $statement->getConnection(), [
                    'type' => 'prepared_statement',
                    'sql' => $statement->getSql(),
                    'params' => $params
                ]
            );
        }
    }

    /**
     * @param string $status
     * @return void
     */
    public function onPreparedStatementExecuted($status) {
        if ($this->isEnabled()) {
            $this->handleProfile($status);
        }
    }

    /**
     * @param DbConnection $connection
     * @param array $profile
     * @return void
     */
    private function initializeProfile($connection, $profile) {
        $this->profile = [];
        $name = $connection->getName();
        if ($name !== null) {
            $this->profile['connection_name'] = $name;
        }
        $this->profile = $this->profile + $profile;
        $this->profile['start_time'] = $this->getTime();
    }

    /**
     * @return float[]
     */
    private function getTime() {
        $segments = explode(' ', microtime());
        $segments[0] = (float)$segments[0];
        $segments[1] = (float)$segments[1];
        return $segments;
    }

    /**
     * @return void
     */
    private function handleProfile($status) {
        if ($this->profile === null) {
            return;
        }
        $profile = $this->profile;
        $this->profile = null;
        $shouldIgnoreRead = Config::getBool(
            'hyperframework.db.operation_profiler.ignore_read', false
        );
        if ($shouldIgnoreRead && isset($profile['sql'])) {
            if (strtoupper(substr(trim($profile['sql']), 0, 6)) === 'SELECT') {
                return;
            }
        }
        $profile['status'] = $status;
        $endTime = $this->getTime();
        $profile['running_time'] = (float)sprintf(
            '%.6F',
            $endTime[1] - $profile['start_time'][1] + $endTime[0]
                - $profile['start_time'][0]
        );
        $profile['start_time'] = DateTime::createFromFormat(
            'U.u', $profile['start_time'][1] . '.'
                . (int)($profile['start_time'][0] * 1000000)
        )->setTimeZone(new DateTimeZone(date_default_timezone_get()));
        DbLogger::debug(function() use ($profile) {
            $log = '[DB] | ';
            if (isset($profile['connection_name'])) {
                $log .= "connection: "
                    . $profile['connection_name'] . " | ";
            }
            $log .= 'status: ' . $profile['status'] . ' | time: ' .
                sprintf('%.6F', $profile['running_time']) . " | ";
            if ($profile['type'] !== 'transaction_operation') {
                $log .= 'sql: ' . $profile['sql'];
                $configName = 'hyperframework.db.operation_profiler'
                    . '.log_prepared_statement_params';
                $shouldLogPreparedStatementParams = Config::getBool(
                    $configName, true
                );
                if ($shouldLogPreparedStatementParams
                    && isset($profile['params'])
                    && count($profile['params']) > 0
                ) {
                    $log .= ' | params: ' . json_encode(
                        $profile['params'],
                        JSON_UNESCAPED_SLASHES
                            | JSON_UNESCAPED_UNICODE
                            | JSON_PRESERVE_ZERO_FRACTION
                    );
                }
            } else {
                $log .= 'transaction: ' . $profile['operation'];
            }
            return $log;
        });
        $profileHandler = $this->getProfileHandler();
        if ($profileHandler !== null) {
            $profileHandler->handle($profile);
        }
    }

    /**
     * @return void
     */
    private function getProfileHandler() {
        if ($this->isProfileHandlerInitialized === false) {
            $classes = Config::getArray(
                'hyperframework.db.operation_profiler.profile_handler_classes'
            );
            $class = Config::getClass(
                'hyperframework.db.operation_profiler.profile_handler_class'
            );
            if ($classes !== null && $class !== null) {
                throw new ConfigException(
                    "Config 'hyperframework.db.operation_profiler"
                        . ".profile_handler_class' conflicts with "
                        . "'hyperframework.db.operation_profiler"
                        . ".profile_handler_classes'."
                );
            }
            if ($class !== null) {
                $this->profileHandler = new $class;
            } elseif ($classes !== null) {
                $this->profileHandler = new DbCompositeProfileHandler;
                foreach ($classes as $class) {
                    $this->profileHandler->addHandler(new $class);
                }
            }
            $this->isProfileHandlerInitialized = true;
        }
        return $this->profileHandler;
    }
}
