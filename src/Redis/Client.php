<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-06 11:27:46 +0800
 */
namespace Teddy\Redis;

class Client
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $default = 'default';

    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var Redis $instances
     */
    protected $instances = [];

    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->default = array_get($settings, 'default', 'default');
        $this->connections = array_get($settings, 'connections', []);
    }

    public function __call($method, array $args)
    {
        $ret = $error = null;
        $redis = $this->getRedis();
        try {
            if (\method_exists($redis, $method)) {
                $ret = $redis->{$method}(...$args);
            }
        } catch (\Exception $e) {
            $error = $e;
        }

        $this->release($redis);
        if ($error) {
            throw $error;
        }

        return $ret;
    }

    protected function getRedis(?string $connection = null): Redis
    {
        $connection = $connection ?: $this->default;
        if (empty($connection) && !isset($this->connections[$connection])) {
            throw new \InvalidArgumentException("Connection [$connection] doesn't exists.");
        }

        if (!isset($this->instances[$connection])) {
            $options = (array) array_get($this->connections, $connection, []);
            $host = array_pull($options, 'host', '127.0.0.1');
            $port = array_pull($options, 'port', 6379);

            $redis = new Redis;
            $redis->connect($host, $port);
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

            $this->instances[$connection] = $redis;
        }

        return $this->instances[$connection];
    }

    protected function release(Redis $redis): bool
    {
        return true;
    }
}
