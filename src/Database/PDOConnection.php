<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-11-30 14:38:37 +0800
 */

namespace Teddy\Database;

use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\Driver as DoctrineDriver;
use Doctrine\DBAL\Schema\AbstractSchemaManager as DoctrineAbstractSchemaManager;
use Doctrine\DBAL\Schema\Column as DoctrineColumn;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PDO;
use PDOException;
use PDOStatement;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Teddy\Database\DBAL\MysqlDriver;
use Teddy\Database\Schema\Builder;
use Teddy\Database\Schema\Grammars\MysqlGrammar;
use Teddy\Database\Schema\MysqlBuilder;

class PDOConnection implements DbConnectionInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $pdo;

    protected $config;

    protected $idleTimeout = 0;

    protected $readOnly = false;

    protected $stick = false;

    protected $schemeBuilder;

    protected $schemeGrammer;

    protected $doctrineConnection;

    public function __construct(array $config, bool $readOnly = false)
    {
        $driver         = 'mysql';
        $host           = Arr::get($config, 'host', '127.0.0.1');
        $port           = Arr::get($config, 'port', 3306);
        $dbName         = Arr::get($config, 'name', '');
        $user           = Arr::get($config, 'user', 'root');
        $password       = Arr::get($config, 'password', '');
        $charset        = Arr::get($config, 'charset', 'utf8mb4');
        $engine         = Arr::get($config, 'engine');
        $collation      = Arr::get($config, 'collation');
        $tablePrefix    = Arr::get($config, 'tablePrefix', '');
        $options        = Arr::get($config, 'options', []);
        $dsn            = $driver.':host='.$host.';port='.$port.';dbname='.$dbName.';charset='.$charset;

        $options      = $options + $this->getDefaultOptions();
        $this->config = compact(
            'driver',
            'dsn',
            'user',
            'password',
            'dbName',
            'options',
            'charset',
            'engine',
            'collation',
            'tablePrefix'
        );

        $this->readOnly    = $readOnly;
        $this->idleTimeout = (int) Arr::get($config, 'idleTimeout', 0);
        $this->pdo         = $this->createPDOConnection();
    }

    public function getConfig(string $key)
    {
        return Arr::get($this->config, $key);
    }

    public function getTablePrefix(): string
    {
        return Arr::get($this->config, 'tablePrefix', '');
    }

    public function getDatabaseName(): string
    {
        return Arr::get($this->config, 'dbName', '');
    }

    public function connect()
    {
        if (!$this->pdo) {
            $this->pdo = $this->createPDOConnection();
        }

        return $this->pdo;
    }

    public function reconnect()
    {
        $this->pdo = $this->createPDOConnection();

        return $this->pdo;
    }

    public function close(): void
    {
        $this->pdo = null;
    }

    public function check()
    {
        if (!$this->pdo) {
            return false;
        }

        try {
            $this->pdo->query('SELECT 1');
        } catch (PDOException $e) {
            log_exception($e);

            return !$this->isDisconnected($e);
        } catch (Exception $e) {
            log_exception($e);

            return false;
        }

        return true;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function beginTransaction(): void
    {
        $this->connect()->beginTransaction();
        $this->stick = true;
    }

    public function rollBack(): void
    {
        $this->connect()->rollBack();
        $this->stick = false;
    }

    public function commit(): void
    {
        $this->connect()->commit();
        $this->stick = false;
    }

    public function query(string $sql, array $data = [], array $options = [])
    {
        $sqlType    = Arr::get($options, 'sqlType');
        $retryTotal = 0;
        $maxRetries = isset($options['maxRetries']) ? intval($options['maxRetries']) : 3;
        $metaInfo   = Arr::get($options, 'metaInfo');
        $pdo        = $this->stick ? $this->pdo : $this->connect();
        $startTime  = microtime(true);

        RETRY:
        $ret   = true;
        $error = $stmt = null;

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $this->bindValues($stmt, $data);
            $stmt->execute();
        } catch (PDOException $e) {
            if (!$this->stick && $retryTotal < $maxRetries && $this->isDisconnected($e)) {
                $pdo = $this->reconnect();
                ++$retryTotal;

                goto RETRY;
            }

            $error = $e;
        } catch (Exception $e) {
            $error = $e;
        }

        if ($error) {
            $stmt && $stmt->closeCursor();
            $this->writeLog($sql, $startTime, $error->getMessage());

            throw $error;
        }

        if (SQL::SELECT_SQL === $sqlType) {
            $fetchType = Arr::get($options, 'fetchType');
            if (SQL::FETCH_ALL === $fetchType) {
                $ret = array_map(function ($data) use ($metaInfo) {
                    return $metaInfo ? $metaInfo->makeInstance($data) : $data;
                }, $stmt->fetchAll());
            } elseif (SQL::FETCH_COLUMN === $fetchType) {
                $ret = $stmt->fetchColumn();
            } else {
                $ret = $stmt->fetch();
                if ($ret && is_array($ret)) {
                    $ret = $metaInfo ? $metaInfo->makeInstance($ret) : $ret;
                }
            }
        } elseif (SQL::INSERT_SQL === $sqlType) {
            if (Arr::get($options, 'lastInsertId')) {
                $ret = $pdo->lastInsertId();
            }
        } else {
            $ret = $stmt->rowCount();
        }

        $stmt && $stmt->closeCursor();
        $this->writeLog($sql, $startTime);

        return $ret;
    }

    public function select(string $sql, array $data = [])
    {
        return $this->query($sql, $data, [
            'sqlType'   => SQL::SELECT_SQL,
            'fetchType' => SQL::FETCH_ALL,
        ]);
    }

    public function getSchemaBuilder(): Builder
    {
        if (!$this->schemeBuilder) {
            if ('mysql' === $this->config['driver']) {
                $this->schemeBuilder = new MysqlBuilder($this);
            } else {
                $this->schemeBuilder = new Builder($this);
            }
        }

        return $this->schemeBuilder;
    }

    public function getSchemaGrammar(): Grammar
    {
        if (!$this->schemeGrammer) {
            if ('mysql' === $this->config['driver']) {
                $this->schemeGrammer = new MysqlGrammar();
            } else {
                $this->schemeGrammer = new Grammar();
            }
        }

        return $this->schemeGrammer;
    }

    public function isDoctrineAvailable(): bool
    {
        return class_exists('Doctrine\DBAL\Connection');
    }

    public function getDoctrineConnection(): DoctrineConnection
    {
        if (is_null($this->doctrineConnection)) {
            $driver = $this->getDoctrineDriver();

            $this->doctrineConnection = new DoctrineConnection([
                'pdo'    => $this->createPDOConnection(),
                'dbname' => $this->getConfig('dbName'),
                'driver' => null,
            ], $driver);
        }

        return $this->doctrineConnection;
    }

    public function getDoctrineSchemaManager(): DoctrineAbstractSchemaManager
    {
        $connection = $this->getDoctrineConnection();

        return $this->getDoctrineDriver()->getSchemaManager(
            $connection,
            $connection->getDatabasePlatform()
        );
    }

    public function getDoctrineColumn(string $table, string $column): DoctrineColumn
    {
        $schema = $this->getDoctrineSchemaManager();

        return $schema->listTableDetails($table)->getColumn($column);
    }

    protected function getDoctrineDriver(): DoctrineDriver
    {
        return new MysqlDriver();
    }

    protected function createPDOConnection(): PDO
    {
        $pdo = new PDO(
            $this->config['dsn'],
            $this->config['user'],
            $this->config['password'],
            $this->config['options']
        );

        if ($this->idleTimeout > 0 && 'mysql' === $this->config['driver']) {
            $pdo->query("SET SESSION interactive_timeout = {$this->idleTimeout};");
            $pdo->query("SET SESSION wait_timeout = {$this->idleTimeout};");
        }

        return $pdo;
    }

    protected function getDefaultOptions(bool $persistent = false): array
    {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ];

        if ($persistent) {
            $options[PDO::ATTR_PERSISTENT] = true;
        }

        return $options;
    }

    protected function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $dataType = PDO::PARAM_STR;
            if (is_int($value)) {
                $dataType = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $dataType = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $dataType = PDO::PARAM_NULL;
            }

            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                $dataType
            );
        }
    }

    protected function isDisconnected(PDOException $e)
    {
        $errorInfo = (array) $e->errorInfo;
        if (isset($errorInfo[1]) && (1461 === $errorInfo[1] || 2006 === $errorInfo[1])) {
            return true;
        }

        $message = $e->getMessage();

        return Str::contains($message, [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'SSL connection has been closed unexpectedly',
        ]);
    }

    protected function writeLog(string $sql, float $start, ?string $extra = null): void
    {
        if ($this->logger) {
            $data = [
                'SQL: '.$sql,
                sprintf('Elapsed time: %.2fms', (microtime(true) - $start) * 1000),
            ];
            if ($extra) {
                $data[] = 'Message: '.$extra;
            }

            $this->logger->info(implode(', ', $data));
        }
    }
}
