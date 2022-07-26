<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 11:15:53 +0800
 */

namespace Teddy\Redis;

use Exception;
use Illuminate\Support\Arr;
use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Pool;

/**
 * @method bool             isConnected()
 * @method bool|string      getHost()
 * @method bool|int         getPort()
 * @method bool|int         getDbNum()
 * @method bool|float       getTimeout()
 * @method bool|float       getReadTimeout()
 * @method null|bool|string getPersistentID()
 * @method null|bool|string getAuth()
 * @method bool             setOption(int $option, mixed $value)
 * @method mixed            getOption(int $option)
 * @method string           ping()
 * @method string           echo(string $message)
 * @method mixed            get(string $key)
 * @method bool             set(string $key, mixed $value, null|array|int $timeout = null)
 * @method bool             setex(string $key, int $ttl, mixed $value)
 * @method bool             psetex(string $key, int $ttl, mixed $value)
 * @method bool             setnx(string $key, mixed $value)
 * @method int              del(array|int|string $key1, int|string $otherKeys)
 * @method mixed            subscribe(string[] $channels, callable $callback)
 * @method mixed            psubscribe(string[] $channels, callable $callback)
 * @method int              publish(string $channel, string $message)
 * @method array|int        pubsub(string $keyword, array|string $argument)
 * @method void             unsubscribe(?array $channels = null)
 * @method void             punsubscribe(?array $patterns = null)
 * @method bool|int         exists(string|string[] $key)
 * @method int              incr(string $key)
 * @method float            incrByFloat(string $key, float $increment)
 * @method int              incrBy(string $key, int $value)
 * @method int              decr(string $key)
 * @method int              decrBy(string $key, int $value)
 * @method bool|int         lPush(string $key, mixed ...$values)
 * @method bool|int         rPush(string $key, mixed ...$values)
 * @method bool|int         lPushx(string $key, mixed $value)
 * @method bool|int         rPushx(string $key, mixed $value)
 * @method mixed            lPop(string $key)
 * @method mixed            rPop(string $key)
 * @method mixed            blPop(string|string[] $keys, int $timeout)
 * @method mixed            brPop(string|string[] $keys, int $timeout)
 * @method bool|int         lLen(string $key)
 * @method bool|int         lSize(string $key)
 * @method mixed            lIndex(string $key, int $index)
 * @method mixed            lGet(string $key, int $index)
 * @method bool             lSet(string $key, int $index, mixed $value)
 * @method array            lRange(string $key, int $start, int $end)
 * @method array            lGetRange(string $key, int $start, int $end)
 * @method array|bool       lTrim(string $key, int $start, int $stop)
 * @method bool|int         lRem(string $key, mixed $value, int $count)
 * @method int              lInsert(string $key, int $position, string $pivot, mixed $value)
 * @method bool|int         sAdd(string $key, mixed ...$values)
 * @method int              sRem(string $key, mixed ...$values)
 * @method bool             sMove(string $srcKey, string $dstKey, mixed $member)
 * @method bool             sIsMember(string $key, mixed $value)
 * @method bool             rename(string $srcKey, string $dstKey)
 * @method bool             renameNx(string $srcKey, string $dstKey)
 * @method bool             expire(string $key, int $ttl)
 * @method bool             pExpire(string $key, int $ttl)
 * @method string[]         keys(string $pattern)
 * @method int              dbSize()
 * @method bool             flushDB()
 * @method bool             flushAll()
 * @method string           info(null|string $option = null)
 * @method bool|int         ttl(string $key)
 * @method bool|int         pttl(string $key)
 * @method bool             mset(array $array)
 * @method array            getMultiple(array $keys)
 * @method array            mget(array $array)
 */
class Redis extends Pool
{
    protected array $config = [];

    public function __construct(array $config)
    {
        parent::__construct($config['pool'] ?? null);
        $this->initConfig($config);
    }

    public function __call(string $method, array $args)
    {
        return $this->runCommand($method, $args);
    }

    public function getNativeClient(): \Redis
    {
        return $this->createConnection()->connect();
    }

    public function runCommand(string $method, array $args)
    {
        $connection = $this->getConnection();

        try {
            $ret = $connection->{$method}(...$args);
        } catch (Exception $e) {
            $this->releaseConnection($connection);

            throw $e;
        }

        $this->releaseConnection($connection);

        return $ret;
    }

    protected function createConnection(): ConnectionInterface
    {
        $config     = Arr::random($this->config);
        $connection = new Connection($config);
        $connection->setPool($this);

        return $connection;
    }

    protected function initConfig(array $config): void
    {
        $defaultConf = [
            'cluster'  => $config['cluster'] ?? false,
            'host'     => $config['host'] ?? '127.0.0.1',
            'port'     => $config['port'] ?? 6379,
            'password' => $config['password'] ?? '',
            'dbIndex'  => $config['dbIndex'] ?? 0,
            'prefix'   => $config['prefix'] ?? '',
        ];

        if (is_array($defaultConf['host']) && !$defaultConf['cluster']) {
            foreach ($defaultConf['host'] as $host) {
                $this->config[] = $this->splitHost($host) + $defaultConf;
            }
        } else {
            $this->config[] = $defaultConf;
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
