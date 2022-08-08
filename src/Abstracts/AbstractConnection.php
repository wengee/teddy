<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:33:06 +0800
 */

namespace Teddy\Abstracts;

use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Pool;

abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * @var null|Pool
     */
    protected $pool;

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
