<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-11 00:05:00 +0800
 */

namespace Teddy\Pool;

use SplQueue;
use Swoole\Coroutine\Channel as CoChannel;
use Teddy\Runtime;

class Channel
{
    protected $size;

    /**
     * @var CoChannel
     */
    protected $channel;

    /**
     * @var SplQueue
     */
    protected $queue;

    public function __construct(int $size)
    {
        $this->size = $size;
        if (Runtime::isSwoole()) {
            $this->channel = new CoChannel($size);
        } else {
            $this->queue = new SplQueue();
        }
    }

    public function pop(float $timeout = 0.0)
    {
        if ($this->channel) {
            return $this->channel->pop($timeout);
        }

        return $this->queue->pop();
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
