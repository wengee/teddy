<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-10 12:05:22 +0800
 */

namespace Teddy\Database;

use Exception;
use Illuminate\Support\Arr;
use PDOException;
use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Channel;
use Teddy\Pool\Pool;
use Throwable;

class Database extends Pool implements DatabaseInterface
{
    protected $hasReadOnly = false;

    protected $readConf = [];

    protected $writeConf = [];

    protected $readChannel;

    protected $currentReadConnections = 0;

    public function __construct(array $config = [])
    {
        parent::__construct($config['pool'] ?? []);
        $this->initConfig($config);

        $this->readChannel = new Channel($this->poolOptions['maxConnections']);
    }

    public function get(bool $readOnly = false): ConnectionInterface
    {
        if ($readOnly && $this->hasReadOnly) {
            return $this->getReadConnection();
        }

        return parent::get();
    }

    public function getReadConnection(): DbConnectionInterface
    {
        if (!$this->hasReadOnly) {
            return $this->getWriteConnection();
        }

        $num = $this->getReadConnectionsInChannel();

        try {
            if ($num === 0 && $this->currentReadConnections < $this->poolOptions['maxConnections']) {
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
        return parent::get();
    }

    public function release(ConnectionInterface $connection): void
    {
        if ($connection->isReadOnly()) {
            $this->readChannel->push($connection);
        } else {
            $this->channel->push($connection);
        }
    }

    public function flush(): void
    {
        parent::flush();

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
        $firstRun = true;
        $pdoConnection = $this->getWriteConnection();

        $ret = false;
        RETRY:
        try {
            $pdoConnection->beginTransaction();
            $transaction = new Transaction($pdoConnection);
            $ret = $callback($transaction);
        } catch (PDOException $e) {
            if ($firstRun) {
                $pdoConnection->rollBack();
                $pdoConnection->reconnect();
                $firstRun = false;
                goto RETRY;
            }

            $pdoConnection->rollBack();
            $this->release($pdoConnection);
            throw $e;
        } catch (Exception $e) {
            $pdoConnection->rollBack();
            $this->release($pdoConnection);
            throw $e;
        }

        if ($ret) {
            $pdoConnection->commit();
        } else {
            $pdoConnection->rollBack();
        }

        $this->release($pdoConnection);
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    public function raw(string $sql): RawSQL
    {
        return new RawSQL($sql);
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

        $config = Arr::random($this->readConf);
        return new PDOConnection($config, true);
    }

    protected function createWriteConnection(): ConnectionInterface
    {
        $config = Arr::random($this->writeConf);
        return new PDOConnection($config, false);
    }

    protected function getReadConnectionsInChannel(): int
    {
        return $this->readChannel->length();
    }

    protected function initConfig(array $config, ?bool $readOnly = null): void
    {
        $defaultConf = [
            'driver'        => 'mysql',
            'host'          => Arr::get($config, 'host', '127.0.0.1'),
            'port'          => Arr::get($config, 'port', 3306),
            'name'          => Arr::get($config, 'name', ''),
            'user'          => Arr::get($config, 'user', ''),
            'password'      => Arr::get($config, 'password', ''),
            'charset'       => Arr::get($config, 'charset', 'utf8mb4'),
            'options'       => Arr::get($config, 'options', []),
            'idleTimeout'   => $this->poolOptions['maxIdleTime'],
        ];

        if ($readOnly === null) {
            if (isset($config['read']) && isset($config['write'])) {
                $this->initConfig($config['read'] + $defaultConf, true);
                $this->initConfig($config['write'] + $defaultConf, false);
            } else {
                $this->initConfig($defaultConf, false);
            }
        } elseif ($readOnly === true) {
            $this->hasReadOnly = true;
            if (is_array($defaultConf['host'])) {
                foreach ($defaultConf['host'] as $host) {
                    $this->readConf[] = $this->splitHost($host) + $defaultConf;
                }
            } else {
                $this->readConf[] = $defaultConf;
            }
        } elseif ($readOnly === false) {
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
        if (strpos($host, ':') === false) {
            $ret['host'] = $host;
        } else {
            $arr = explode(':', $host, 2);
            $ret['host'] = $arr[0];
            $ret['port'] = intval($arr[1]);
        }

        return $ret;
    }
}
