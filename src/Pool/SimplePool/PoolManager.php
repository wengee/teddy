<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:13:48 +0800
 */

namespace Teddy\Pool\SimplePool;

class PoolManager
{
    protected array $pools = [];

    public function get(string $name, callable $callback, array $options = []): Pool
    {
        if (!isset($this->pools[$name])) {
            $this->pools[$name] = new Pool($callback, $options);
        }

        return $this->pools[$name];
    }
}
