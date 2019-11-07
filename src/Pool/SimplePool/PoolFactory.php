<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-07 17:37:43 +0800
 */

namespace Teddy\Pool\SimplePool;

use Teddy\Traits\Singleton;

class PoolFactory
{
    use Singleton;

    protected $pools = [];

    public function get(string $name, callable $callback, array $options = []): Pool
    {
        if (!isset($this->pools[$name])) {
            $this->pools[$name] = new Pool($callback, $options);
        }

        return $this->pools[$name];
    }
}
