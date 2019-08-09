<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 18:56:02 +0800
 */

namespace Teddy\Redis;

use BadMethodCallException;
use Exception;
use Teddy\Interfaces\ConnectionInterface;

class Connection implements ConnectionInterface
{
    protected $redis = null;

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config + [
            'host' => '127.0.0.1',
            'port' => 6379,
        ];
    }

    public function __call(string $method, array $args)
    {
        $redis = $this->connect();
        if (method_exists($redis, $method)) {
            return $redis->{$method}(...$args);
        }

        throw new BadMethodCallException(sprintf('The method[%s] is not defined.', $method));
    }

    public function connect()
    {
        if (!$this->redis) {
            $this->redis = $this->createRedisClient();
        }

        return $this->redis;
    }

    public function reconnect()
    {
        $this->redis = $this->createRedisClient();
        return $this->redis;
    }

    public function close()
    {
        $this->redis = null;
    }

    public function check()
    {
        if (!$this->redis) {
            return false;
        }

        try {
            $this->redis->ping();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function createRedisClient(): \Redis
    {
        $redis = new \Redis;
        $redis->connect($this->config['host'], $this->config['port']);
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        if (isset($this->config['password']) && $this->config['password']) {
            $redis->auth($this->config['password']);
        }

        if (isset($this->config['dbIndex']) && $this->config['dbIndex'] > 0) {
            $redis->select((int) $this->config['dbIndex']);
        }

        if (isset($this->config['prefix']) && $this->config['prefix']) {
            $redis->setOption(\Redis::OPT_PREFIX, $this->config['prefix']);
        }

        return $redis;
    }
}
