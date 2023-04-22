<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-22 10:52:56 +0800
 */

namespace Teddy\Database;

use Exception;
use Illuminate\Support\Arr;
use PDOException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Teddy\Database\Traits\DatabaseTrait;
use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Channel;
use Teddy\Pool\Pool;
use Throwable;

class Database extends Pool implements DatabaseInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use DatabaseTrait;

    protected string $driver = '';

    protected bool $hasReadOnly = false;

    protected array $readConf = [];

    protected array $writeConf = [];

    protected ?ConnectionInterface $readInstance = null;

    protected ?Channel $readChannel = null;

    protected int $currentReadConnections = 0;

    public function __construct(array $config = [])
    {
        parent::__construct($config['pool'] ?? null);
        $this->driver = $config['driver'] ?? 'mysql';
        $this->initConfig($config);

        if ($this->poolOptions) {
            $this->readChannel = new Channel($this->poolOptions['maxConnections']);
        }
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getConnection(bool $readOnly = false): ConnectionInterface
    {
        if ($readOnly && $this->hasReadOnly) {
            return $this->getReadConnection();
        }

        return parent::getConnection();
    }

    public function getReadConnection(): DbConnectionInterface
    {
        if (!$this->hasReadOnly) {
            return $this->getWriteConnection();
        }

        if (!$this->poolOptions) {
            if (!$this->readInstance) {
                $this->readInstance = $this->createReadConnection();
            }

            return $this->readInstance;
        }

        $num = $this->getReadConnectionsInChannel();

        try {
            if (0 === $num && $this->currentReadConnections < $this->poolOptions['maxConnections']) {
                ++$this->currentReadConnections;

                return $this->createReadConnection();
            }
        } catch (Throwable $throwable) {
            --$this->currentReadConnections;

            throw $throwable;
        }

        return $this->readChannel->pop($this->poolOptions['waitTimeout']);
    }

    public function getWriteConnection(): DbConnectionInterface
    {
        return parent::getConnection();
    }

    public function releaseConnection(ConnectionInterface $connection): void
    {
        if (!$this->poolOptions) {
            return;
        }

        /**
         * @var PDOConnection $connection
         */
        if ($connection->isReadOnly()) {
            $this->readChannel->push($connection);
        } else {
            $this->channel->push($connection);
        }
    }

    public function flushConnections(): void
    {
        if (!$this->poolOptions) {
            return;
        }

        parent::flushConnections();

        $num = $this->getReadConnectionsInChannel();
        if ($num > 0) {
            while ($this->currentReadConnections > $this->poolOptions['minConnections'] && $conn = $this->readChannel->pop($this->poolOptions['waitTimeout'])) {
                $conn->close();
                --$this->currentReadConnections;
            }
        }
    }

    public function transaction(callable $callback): void
    {
        $firstRun      = true;
        $pdoConnection = $this->getWriteConnection();

        $ret = false;
        RETRY:
        try {
            $pdoConnection->beginTransaction();
            $transaction = new Transaction($pdoConnection);
            $ret         = $callback($transaction);
        } catch (PDOException $e) {
            if ($firstRun) {
                $pdoConnection->rollBack();
                $pdoConnection->reconnect();
                $firstRun = false;

                goto RETRY;
            }

            $pdoConnection->rollBack();
            $this->releaseConnection($pdoConnection);

            throw $e;
        } catch (Exception $e) {
            $pdoConnection->rollBack();
            $this->releaseConnection($pdoConnection);

            throw $e;
        }

        if ($ret) {
            $pdoConnection->commit();
        } else {
            $pdoConnection->rollBack();
        }

        $this->releaseConnection($pdoConnection);
    }

    protected function createConnection(): ConnectionInterface
    {
        return $this->createWriteConnection();
    }

    protected function createReadConnection(): ConnectionInterface
    {
        if (!$this->hasReadOnly) {
            return $this->createWriteConnection();
        }

        $config           = Arr::random($this->readConf);
        $config['driver'] = $this->driver;

        $pdo = new PDOConnection($config, true);
        $pdo->setPool($this);
        if ($this->logger) {
            $pdo->setLogger($this->logger);
        }

        return $pdo;
    }

    protected function createWriteConnection(): ConnectionInterface
    {
        $config           = Arr::random($this->writeConf);
        $config['driver'] = $this->driver;

        $pdo = new PDOConnection($config, false);
        $pdo->setPool($this);
        if ($this->logger) {
            $pdo->setLogger($this->logger);
        }

        return $pdo;
    }

    protected function getReadConnectionsInChannel(): int
    {
        if (!$this->poolOptions) {
            return $this->readInstance ? 1 : 0;
        }

        return $this->readChannel->length();
    }

    protected function initConfig(array $config, ?bool $readOnly = null): void
    {
        $defaultConf = [
            'host'        => $config['host'] ?? '127.0.0.1',
            'port'        => $config['port'] ?? 3306,
            'database'    => $config['name'] ?? $config['database'] ?? '',
            'user'        => $config['user'] ?? '',
            'password'    => $config['password'] ?? '',
            'charset'     => $config['charset'] ?? 'utf8mb4',
            'options'     => $config['options'] ?? [],
            'idleTimeout' => 900,
        ];

        if ($this->poolOptions) {
            $defaultConf['idleTimeout'] = $this->poolOptions['maxIdleTime'];
        }

        if (null === $readOnly) {
            if (isset($config['read'], $config['write'])) {
                $this->initConfig($config['read'] + $defaultConf, true);
                $this->initConfig($config['write'] + $defaultConf, false);
            } else {
                $this->initConfig($defaultConf, false);
            }
        } elseif (true === $readOnly) {
            $this->hasReadOnly = true;
            if (is_array($defaultConf['host'])) {
                foreach ($defaultConf['host'] as $host) {
                    $this->readConf[] = $this->splitHost($host) + $defaultConf;
                }
            } else {
                $this->readConf[] = $defaultConf;
            }
        } elseif (false === $readOnly) {
            if (is_array($defaultConf['host'])) {
                foreach ($defaultConf['host'] as $host) {
                    $this->writeConf[] = $this->splitHost($host) + $defaultConf;
                }
            } else {
                $this->writeConf[] = $defaultConf;
            }
        }
    }

    protected function splitHost(string $host): array
    {
        $ret = [];
        if (false === strpos($host, ':')) {
            $ret['host'] = $host;
        } else {
            $arr         = explode(':', $host, 2);
            $ret['host'] = $arr[0];
            $ret['port'] = intval($arr[1]);
        }

        return $ret;
    }
}
