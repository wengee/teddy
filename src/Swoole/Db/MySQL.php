<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-18 16:51:25 +0800
 */
namespace Teddy\Swoole\Db;

use Swoole\Coroutine\MySQL as SwooleMySQL;

class MySQL extends SwooleMySQL
{
    protected $identity = null;

    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    public function connect(array $config)
    {
        $host     = array_get($config, 'host', '127.0.0.1');
        $port     = array_get($config, 'port', 3306);
        $name     = array_get($config, 'name', '');
        $user     = array_get($config, 'user', 'root');
        $password = array_get($config, 'password', '');
        $charset  = array_get($config, 'charset', 'utf8mb4');

        return parent::connect([
            'host' => $host,
            'port' => $port,
            'database' => $name,
            'user' => $user,
            'password' => $password,
            'charset' => $charset,
            'timeout' => 3,
            'strict_type' => true,
            'fetch_mode' => true,
        ]);
    }

    public function getIdentity()
    {
        return $this->identity;
    }
}
