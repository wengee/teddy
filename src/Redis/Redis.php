<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-19 21:25:31 +0800
 */

namespace Teddy\Redis;

use Exception;
use Illuminate\Support\Arr;
use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Pool;

/**
 * @see https://github.com/ukko/phpredis-phpdoc
 *
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
 * @method bool|int         hSet(string $key, string $hashKey, string $value)
 * @method bool             hSetNx(string $key, string $hashKey, string $value)
 * @method string           hGet(string $key, string $hashKey)
 * @method bool|int         hLen(string $key)
 * @method bool|int         hDel(string $key, string $hashKey, string ...$otherHashKeys)
 * @method array            hKeys(string $key)
 * @method array            hVals(string $key)
 * @method array            hGetAll(string $key)
 * @method bool             hExists(string $key, string $hashKey)
 * @method int              hIncrBy(string $key, string $hashKey, int $value)
 * @method float            hIncrByFloat(string $key, string $hashKey, float $value)
 * @method bool             hMSet(string $key, array $hashKeys)
 * @method array            hMGet(string $key, array $hashKeys)
 * @method array            hScan(string $key, int &$iterator, ?string $pattern = null, int $count = 0)
 * @method int              hStrLen(string $key, string $field)
 * @method int              zAdd(string $key, array $options, float $score1, mixed|string $value1, float $score2 = null, mixed|string $value2 = null, float $scoreN = null, mixed|string $valueN = null)
 * @method array            zRange(string $key, int $start, int $end, ?bool $withscores = null)
 * @method int              zRem(string $key, mixed|string $member1, string|mixed  ...$otherMembers)
 * @method int              zDelete(string $key, mixed|string $member1, string|mixed ...$otherMembers)
 * @method array            zRevRange(string $key, int $start, int $end, ?bool $withscore = null)
 * @method array            zRangeByScore(string $key, int $start, int $end, array $options = [])
 * @method array            zRevRangeByScore(string $key, int $start, int $end, array $options = [])
 * @method array|bool       zRangeByLex(string $key, int $min, int $max, ?int $offset = null, ?int $limit = null)
 * @method array|bool       zRevRangeByLex(string $key, int $min, int $max, ?int $offset = null, ?int $limit = null)
 * @method int              zCount(string $key, int|string $start, int|string $end)
 * @method int              zRemRangeByScore(string $key, float|string $start, float|string $end)
 * @method void             zDeleteRangeByScore(string $key, float $start, float $end)
 */
class Redis extends Pool
{
    /**
     * @var array
     */
    protected $config = [];

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
