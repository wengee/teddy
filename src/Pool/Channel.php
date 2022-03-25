<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-16 14:41:59 +0800
 */

namespace Teddy\Pool;

use SplQueue;

class Channel
{
    protected $size;

    protected $channel;

    protected $queue;

    public function __construct(int $size)
    {
        $this->size  = $size;
        $this->queue = new SplQueue();
    }

    public function pop()
    {
        if ($this->queue->isEmpty()) {
            return null;
        }

        return $this->queue->pop();
    }

    public function push($data)
    {
        return $this->queue->push($data);
    }

    public function length(): int
    {
        return $this->queue->count();
    }
}
