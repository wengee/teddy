<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-06 15:18:53 +0800
 */
namespace Teddy\Swoole\Db;

use Teddy\Db\Database as SyncDatabase;
use Teddy\Db\PDO;
use Teddy\Swoole\ConnectionPool;

class Database extends SyncDatabase
{
    /**
     * @var array
     */
    protected $pool = [];

    protected function getPdo(?string $connection = null, bool $master = true): PDO
    {
        $connection = $connection ?: $this->default;
        if (empty($connection) && !isset($this->connections[$connection])) {
            throw new \InvalidArgumentException("Connection [$connection] doesn't exists.");
        }

        if (!isset($this->pool[$connection])) {
            $this->pool[$connection] = ['master' => null, 'slave' => null];
        }

        $key = $master ? 'master' : 'slave';
        $connectionKey = $connection . '-' . $key;
        if (empty($this->pool[$connectionKey])) {
            $poolOptions = array_get($this->settings, 'pool', []);

            $single = false;
            $config = array_get($this->connections, $connection, []);
            if (isset($config['master'])) {
                $config = isset($config[$key]) ? $config[$key] : $config['master'];
            } else {
                $single = true;
            }

            $pool = new ConnectionPool($poolOptions, function () use ($config, $connectionKey, $poolOptions) {
                if (isset($config[0]) && !isset($config['host'])) {
                    $config = array_random($config);
                }

                $pdo = new PDO($config, $connectionKey);

                $idleTimeout = (int) array_get($poolOptions, 'idleTimeout', 0);
                if ($idleTimeout > 0) {
                    $pdo->query("SET SESSION interactive_timeout = {$idleTimeout};");
                    $pdo->query("SET SESSION wait_timeout = {$idleTimeout};");
                }
                return $pdo;
            });

            if ($single) {
                $this->pool[$connection . '-master'] = $pool;
                $this->pool[$connection . '-slave'] = $pool;
            } else {
                $this->pool[$connectionKey] = $pool;
            }
        }

        return $this->pool[$connectionKey]->get();
    }

    protected function release(PDO $pdo, bool $renew = false)
    {
        $connectionKey = $pdo->getIdentity() ?: [null, null];
        if ($connectionKey && isset($this->pool[$connectionKey])) {
            if ($renew) {
                return $this->pool[$connectionKey]->renew($pdo);
            } else {
                $this->pool[$connectionKey]->put($pdo);
            }
        }
    }
}
