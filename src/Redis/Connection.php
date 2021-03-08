<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-08 21:07:43 +0800
 */

namespace Teddy\Redis;

use BadMethodCallException;
use Exception;
use Teddy\Interfaces\ConnectionInterface;

class Connection implements ConnectionInterface
{
    protected $redis;

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config + [
            'cluster' => false,
            'host'    => '127.0.0.1',
            'port'    => 6379,
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
            $this->redis = $this->createClient();
        }

        return $this->redis;
    }

    public function reconnect()
    {
        $this->redis = $this->createClient();

        return $this->redis;
    }

    public function close(): void
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

    protected function createClient()
    {
        if ($this->config['cluster']) {
            return $this->createRedisClusterClient();
        }

        return $this->createRedisClient();
    }

    protected function createRedisClient(): \Redis
    {
        $redis = new \Redis();
        $redis->connect($this->config['host'], $this->config['port']);
        $redis->setOption(\Redis::OPT_SERIALIZER, (string) \Redis::SERIALIZER_PHP);

        if (isset($this->config['password']) && $this->config['password']) {
            $redis->auth($this->config['password']);
        }

        if (isset($this->config['dbIndex']) && $this->config['dbIndex'] > 0) {
            $redis->select((int) $this->config['dbIndex']);
        }

        if (isset($this->config['prefix']) && $this->config['prefix']) {
            $redis->setOption(\Redis::OPT_PREFIX, $this->config['prefix']);
        }

        $options = $this->config['options'] ?? [];
        if ($options && is_array($options)) {
            foreach ($options as $key => $value) {
                $redis->setOption($key, $value);
            }
        }

        return $redis;
    }

    protected function createRedisClusterClient(): \RedisCluster
    {
        $host = $this->config['host'];
        if (!is_array($host)) {
            $host = [$host];
        }

        $timeout     = $this->config['timeout'] ?? 1.5;
        $readTimeout = $this->config['readTimeout'] ?? 3.0;
        $password    = $this->config['password'] ?? '';

        $redis = new \RedisCluster(null, $host, $timeout, $readTimeout, false, $password);
        $redis->setOption(\Redis::OPT_SERIALIZER, (string) \Redis::SERIALIZER_PHP);

        if (isset($this->config['prefix']) && $this->config['prefix']) {
            $redis->setOption(\Redis::OPT_PREFIX, $this->config['prefix']);
        }

        $options = $this->config['options'] ?? [];
        if ($options && is_array($options)) {
            foreach ($options as $key => $value) {
                $redis->setOption($key, $value);
            }
        }

        return $redis;
    }
}
