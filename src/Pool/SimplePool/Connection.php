<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-07 17:28:20 +0800
 */

namespace Teddy\Pool\SimplePool;

use Teddy\Interfaces\ConnectionInterface;

class Connection implements ConnectionInterface
{
    protected $lastUseTime = 0.0;

    protected $pool;

    protected $callback;

    protected $connection;

    public function __construct(Pool $pool, callable $callback)
    {
        $this->pool = $pool;
        $this->callback = $callback;
    }

    public function connect()
    {
        if (!$this->connection || !$this->check()) {
            return $this->reconnect();
        }

        return $this->connection;
    }

    public function reconnect()
    {
        $this->connection = ($this->callback)();
        $this->lastUseTime = microtime(true);
        return $this->connection;
    }

    public function close(): void
    {
        $this->connection = null;
    }

    public function check()
    {
        $maxIdleTime = $this->pool->getOption('maxIdleTime');
        $now = microtime(true);
        if ($now > $maxIdleTime + $this->lastUseTime) {
            return false;
        }
        $this->lastUseTime = $now;
        return true;
    }
}
