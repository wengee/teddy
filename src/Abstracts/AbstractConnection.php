<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-04-02 10:48:56 +0800
 */

namespace Teddy\Abstracts;

use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Pool;

abstract class AbstractConnection implements ConnectionInterface
{
    protected ?Pool $pool;

    public function setPool(Pool $pool): void
    {
        $this->pool = $pool;
    }

    public function release(): void
    {
        if ($this->pool) {
            $this->pool->releaseConnection($this);
        }
    }
}
