<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-12 16:47:27 +0800
 */

namespace Teddy\Pool;

use SplQueue;
use Swoole\Coroutine\Channel as CoChannel;
use Teddy\Swoole\Coroutine;

class Channel
{
    protected $size;

    protected $channel;

    protected $queue;

    public function __construct(int $size)
    {
        $this->size = $size;
        $this->channel = new CoChannel($size);
        $this->queue = new SplQueue;
    }

    public function pop(float $timeout)
    {
        if ($this->isCoroutine()) {
            return $this->channel->pop($timeout);
        }

        return $this->queue->shift();
    }

    public function push($data)
    {
        if ($this->isCoroutine()) {
            return $this->channel->push($data);
        }

        return $this->queue->push($data);
    }

    public function length(): int
    {
        if ($this->isCoroutine()) {
            return $this->channel->length();
        }

        return $this->queue->count();
    }

    protected function isCoroutine(): bool
    {
        return Coroutine::id() > 0;
    }
}
