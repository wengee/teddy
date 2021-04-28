<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-28 16:32:36 +0800
 */

namespace Teddy\Pool;

use SplQueue;
use Swoole\Coroutine\Channel as CoChannel;

class Channel
{
    protected $size;

    protected $channel;

    protected $queue;

    public function __construct(int $size)
    {
        $this->size  = $size;

        if (class_exists(CoChannel::class)) {
            $this->channel = new CoChannel($size);
        } else {
            $this->queue = new SplQueue();
        }
    }

    public function pop(float $timeout)
    {
        if ($this->channel) {
            return $this->channel->pop($timeout);
        }

        return $this->queue->shift();
    }

    public function push($data)
    {
        if ($this->channel) {
            return $this->channel->push($data);
        }

        return $this->queue->push($data);
    }

    public function length(): int
    {
        if ($this->channel) {
            return $this->channel->length();
        }

        return $this->queue->count();
    }
}
