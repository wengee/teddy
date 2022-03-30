<?php

namespace Teddy\Abstracts;

use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Pool;

abstract class AbstractConnection implements ConnectionInterface
{
    protected $pool;

    public function setPool(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function release()
    {
        if ($this->pool) {
            $this->pool->release($this);
        }
    }
}
