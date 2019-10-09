<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-10-09 16:50:18 +0800
 */

namespace Teddy\Redis;

use Exception;
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
        $config = array_random($this->config);
        return new Connection($config);
    }

    protected function initConfig(array $config): void
    {
        $defaultConf = [
            'host'      => array_get($config, 'host', '127.0.0.1'),
            'port'      => array_get($config, 'port', 6379),
            'password'  => array_get($config, 'password', ''),
            'dbIndex'   => array_get($config, 'dbIndex', 0),
            'prefix'    => array_get($config, 'prefix', ''),
        ];

        if (is_array($defaultConf['host'])) {
            foreach ($defaultConf['host'] as $host) {
                $this->config[] = ['host' => $host] + $defaultConf;
            }
        } else {
            $this->config[] = $defaultConf;
        }
    }
}
