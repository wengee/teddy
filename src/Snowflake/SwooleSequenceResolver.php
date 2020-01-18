<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-18 16:51:26 +0800
 */

namespace Teddy\Snowflake;

use Godruoyi\Snowflake\SequenceResolver;
use Swoole\Lock;

class SwooleSequenceResolver implements SequenceResolver
{
    protected $lastTimestamp = -1;

    protected $sequence = 0;

    protected $lock;

    public function __construct()
    {
        $this->lock = new Lock(SWOOLE_MUTEX);
    }

    public function sequence(int $currentTime)
    {
        if ($this->lock->lock()) {
            if ($this->lastTimeStamp === $currentTime) {
                ++$this->sequence;
            } else {
                $this->sequence = 0;
            }

            $this->lastTimeStamp = $currentTime;
            $this->lock->unlock();
            return $this->sequence;
        }
    }
}
