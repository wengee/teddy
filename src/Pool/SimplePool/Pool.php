<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Pool\SimplePool;

use Teddy\Interfaces\ConnectionInterface;
use Teddy\Pool\Pool as AbstractPool;

class Pool extends AbstractPool
{
    protected $callback;

    public function __construct(callable $callback, array|null|bool $options = [])
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
        $connection = new Connection($this, $this->callback);
        $connection->setPool($this);
        return $connection;
    }
}
