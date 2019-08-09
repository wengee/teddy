<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-08 18:14:55 +0800
 */

namespace Teddy\Database;

use Teddy\Exception;

class Manager
{
    protected $config = [];

    protected $pools = [];

    public function __construct()
    {
        $config = config('database');
        if ($config && is_array($config)) {
            $this->config = $config;
        }
    }

    public function connection(?string $key = null): Database
    {
        $key = $key ?: 'default';
        if (!isset($this->pools[$key])) {
            if (!isset($this->config[$key]) || !is_array($this->config[$key])) {
                throw new Exception('Can not found the database config.');
            }

            $this->pools[$key] = new Database($this->config[$key]);
        }

        return $this->pools[$key];
    }

    public function __call(string $method, array $args)
    {
        $connection = $this->connection();
        return $connection->{$method}(...$args);
    }
}
