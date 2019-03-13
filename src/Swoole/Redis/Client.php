<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-06 11:28:29 +0800
 */
namespace Teddy\Swoole\Redis;

use Teddy\Redis\Client as SyncClient;
use Teddy\Redis\Redis;
use Teddy\Swoole\ConnectionPool;

class Client extends SyncClient
{
    /**
     * @var array
     */
    protected $pool = [];

    protected function getRedis(?string $connection = null): Redis
    {
        $connection = $connection ?: $this->default;
        if (empty($connection) && !isset($this->connections[$connection])) {
            throw new \InvalidArgumentException("Connection [$connection] doesn't exists.");
        }

        if (!isset($this->pool[$connection])) {
            $poolOptions = array_get($this->settings, 'pool', []);

            $options = (array) array_get($this->connections, $connection, []);
            $pool = new ConnectionPool($poolOptions, function () use ($options, $connection) {
                $host = array_pull($options, 'host', '127.0.0.1');
                $port = array_pull($options, 'port', 6379);

                $redis = new Redis;
                $redis->connect($host, $port);
                $redis->setConnection($connection);
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

                $password = array_pull($options, 'password');
                if ($password) {
                    $redis->auth($password);
                }

                $dbindex = (int) array_pull($options, 'dbindex');
                if ($dbindex > 0) {
                    $redis->select($dbindex);
                }

                $prefix = array_pull($options, 'prefix');
                if ($prefix) {
                    $redis->setOption(\Redis::OPT_PREFIX, $prefix);
                }

                return $redis;
            });

            $this->pool[$connection] = $pool;
        }

        return $this->pool[$connection]->get();
    }

    protected function release(Redis $redis): bool
    {
        $connection = $redis->getConnection();
        if ($connection && isset($this->pool[$connection])) {
            $this->pool[$connection]->put($redis);
        }

        return true;
    }
}
