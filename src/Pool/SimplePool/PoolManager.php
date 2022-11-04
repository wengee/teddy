<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-24 11:35:58 +0800
 */

namespace Teddy\Pool\SimplePool;

class PoolManager
{
    protected $pools = [];

    public function get(string $name, callable $callback, array $options = []): Pool
    {
        if (!isset($this->pools[$name])) {
            $this->pools[$name] = new Pool($callback, $options);
        }

        return $this->pools[$name];
    }
}