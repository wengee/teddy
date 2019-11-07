<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-07 18:25:40 +0800
 */

namespace Teddy\Pool\SimplePool;

use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Pool as AbstractPool;

class Pool extends AbstractPool
{
    protected $callback;

    public function __construct(callable $callback, array $options = [])
    {
        $this->callback = $callback;
        parent::__construct($options);
    }

    public function getOption(string $key)
    {
        return $this->poolOptions[$key];
    }

    protected function createConnection(): ConnectionInterface
    {
        echo time() . PHP_EOL;
        return new Connection($this, $this->callback);
    }
}
