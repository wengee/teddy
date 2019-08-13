<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-13 14:46:02 +0800
 */
namespace Teddy;

use Exception;
use InvalidArgumentException;
use Swoole\Timer;

abstract class Task
{
    /**
     * @var integer
     */
    protected $delay = 0;

    /**
     * @var integer
     */
    protected $executionTime = 600;

    /**
     * @var bool
     */
    protected $exclusive = true;

    public function delay(int $delay): self
    {
        if ($delay <= 0) {
            throw new InvalidArgumentException('The delay must be greater than 0');
        }

        $this->delay = $delay;
        return $this;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function send(int $delay = 0)
    {
        if ($delay > 0) {
            $this->delay($delay);
        }

        return static::deliver($this);
    }

    public function setExclusive(int $executionTime)
    {
        if ($executionTime > 0) {
            $this->executionTime = $executionTime;
            $this->exclusive = true;
        } else {
            $this->exclusive = false;
        }
    }

    final public function safeRun()
    {
        safe_call([$this, 'run']);
    }

    final public function run()
    {
        if ($this->tryLock()) {
            try {
                $ret = $this->handle();
            } catch (Exception $e) {
                $this->tryLock(true);
                throw $e;
            }

            $this->tryLock(true);
            return $ret;
        }
    }

    protected function tryLock(bool $unlock = false): bool
    {
        $redis = app('redis');
        if (!$redis || !$this->exclusive) {
            return true;
        }

        $cacheKey = 'teddyTask:lock:' . strtr(get_class($this), '\\', '');
        if ($unlock) {
            $redis->delete($cacheKey);
            return true;
        } else {
            if ($redis->exists($cacheKey)) {
                return false;
            } else {
                $redis->set($cacheKey, true, $this->executionTime);
                return true;
            }
        }
    }

    abstract protected function handle();

    public static function deliver(Task $task)
    {
        $deliver = function () use ($task) {
            $swoole = app('swoole');
            if ($swoole) {
                return $swoole->task($task);
            }
        };

        if (defined('IN_SWOOLE') && IN_SWOOLE && $task->getDelay() > 0) {
            return Timer::after($task->getDelay() * 1000, $deliver);
        } else {
            return $deliver();
        }
    }
}
