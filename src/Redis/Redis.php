<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 16:37:27 +0800
 */

namespace Teddy\Redis;

use Exception;
use Illuminate\Support\Arr;
use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Pool;

class Redis extends Pool
{
    protected $config = [];

    public function __construct(array $config)
    {
        parent::__construct($config['pool'] ?? []);
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
        $connection = $this->get();

        try {
            $ret = $connection->{$method}(...$args);
        } catch (Exception $e) {
            $this->release($connection);

            throw $e;
        }

        $this->release($connection);

        return $ret;
    }

    protected function createConnection(): ConnectionInterface
    {
        $config = Arr::random($this->config);

        return new Connection($config);
    }

    protected function initConfig(array $config): void
    {
        $defaultConf = [
            'cluster'   => Arr::get($config, 'cluster', false),
            'host'      => Arr::get($config, 'host', '127.0.0.1'),
            'port'      => Arr::get($config, 'port', 6379),
            'password'  => Arr::get($config, 'password', ''),
            'dbIndex'   => Arr::get($config, 'dbIndex', 0),
            'prefix'    => Arr::get($config, 'prefix', ''),
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
