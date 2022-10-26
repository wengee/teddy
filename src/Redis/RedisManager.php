<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-24 11:33:01 +0800
 */

namespace Teddy\Redis;

use Teddy\Exception;

class RedisManager
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $pools = [];

    public function __construct()
    {
        $config = config('redis');
        if ($config && is_array($config)) {
            $this->config = $config;
        }
    }

    public function __call(string $method, array $args)
    {
        $connection = $this->connection();

        return $connection->runCommand($method, $args);
    }

    public function connection(?string $key = null): Redis
    {
        $key = $key ?: 'default';
        if (!isset($this->pools[$key])) {
            if (!isset($this->config[$key]) || !is_array($this->config[$key])) {
                throw new Exception('Can not found the redis config.');
            }

            $this->pools[$key] = new Redis($this->config[$key]);
        }

        return $this->pools[$key];
    }
}
