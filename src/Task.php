<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 19:01:04 +0800
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

    public function setExclusive(int $executionTime)
    {
        if ($executionTime > 0) {
            $this->executionTime = $executionTime;
            $this->exclusive = true;
        } else {
            $this->exclusive = false;
        }
    }

    final public function send(int $delay = 0): void
    {
        $deliver = function () {
            app('swoole')->task($this);
        };

        if ($delay <= 0) {
            $delay = $this->getDelay();
        }

        if (defined('IN_SWOOLE') && IN_SWOOLE && $delay > 0) {
            Timer::after($delay * 1000, $deliver);
        } else {
            $deliver();
        }
    }

    final public function result(float $timeout = 3.0)
    {
        $ret = app('swoole')->taskCo([$this], $timeout);
        return isset($ret[0]) ? $ret[0] : false;
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

        throw new Exception('Task is running.');
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
}
