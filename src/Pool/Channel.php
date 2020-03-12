<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Pool;

use SplQueue;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel as CoChannel;

class Channel
{
    protected $size;

    protected $channel;

    protected $queue;

    public function __construct(int $size)
    {
        $this->size = $size;
        $this->queue = new SplQueue;

        if (class_exists(CoChannel::class)) {
            $this->channel = new CoChannel($size);
        }
    }

    public function pop(float $timeout)
    {
        if ($this->isCoroutine() && $this->channel) {
            return $this->channel->pop($timeout);
        }

        return $this->queue->shift();
    }

    public function push($data)
    {
        if ($this->isCoroutine() && $this->channel) {
            return $this->channel->push($data);
        }

        return $this->queue->push($data);
    }

    public function length(): int
    {
        if ($this->isCoroutine() && $this->channel) {
            return $this->channel->length();
        }

        return $this->queue->count();
    }

    protected function isCoroutine(): bool
    {
        return class_exists(Coroutine::class) && Coroutine::getCid() > 0;
    }
}
