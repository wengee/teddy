<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-26 11:50:58 +0800
 */

namespace Teddy;

use Exception;
use InvalidArgumentException;
use Swoole\Timer;
use Teddy\Swoole\Coroutine;

abstract class Task
{
    /**
     * @var float
     */
    protected $delay = 0;

    /**
     * @var float
     */
    protected $waitTimeout = 0;

    /**
     * @var integer
     */
    protected $executionTime = 600;

    /**
     * @var bool
     */
    protected $exclusive = true;

    /**
     * @var mixed
     */
    protected $result = false;

    final public function delay(float $delay): self
    {
        if ($delay <= 0) {
            throw new InvalidArgumentException('The delay must be greater than 0');
        }

        $this->delay = $delay;
        return $this;
    }

    final public function wait(float $waitTimeout = 3.0): self
    {
        if ($waitTimeout <= 0) {
            throw new InvalidArgumentException('The waitTimeout must be greater than 0');
        }

        $this->waitTimeout = $waitTimeout;
        return $this;
    }

    final public function exclusive(int $executionTime): self
    {
        if ($executionTime > 0) {
            $this->executionTime = $executionTime;
            $this->exclusive = true;
        } else {
            $this->exclusive = false;
        }

        return $this;
    }

    final public function isWaiting(): bool
    {
        return $this->waitTimeout > 0;
    }

    final public function sendWait(float $waitTimeout = 3.0)
    {
        $this->wait($waitTimeout);
        $ret = app('swoole')->taskCo([$this], $this->waitTimeout);
        return $ret && isset($ret[0]) ? $ret[0] : false;
    }

    final public function send()
    {
        if ($this->isWaiting()) {
            return $this->sendWait();
        } else {
            $this->deliver();
        }
    }

    final public function finish()
    {
        return $this->result;
    }

    final public function safeRun(): void
    {
        safe_call([$this, 'run']);
    }

    final public function run()
    {
        if ($this->tryLock()) {
            try {
                $ret = $this->handle();
            } catch (Exception $e) {
                $this->unLock();
                $this->result = false;
                throw $e;
            }

            $this->unLock();
            $this->result = $ret;
            return $ret;
        }

        $this->result = false;
        throw new Exception('Task is running.');
    }

    protected function tryLock(): bool
    {
        $redis = app('redis');
        if (!$redis || !$this->exclusive) {
            return true;
        }

        $cacheKey = 'teddyTask:lock:' . strtr(get_class($this), '\\', '');
        if ($redis->exists($cacheKey)) {
            return false;
        } else {
            $redis->set($cacheKey, true, $this->executionTime);
            return true;
        }
    }

    protected function unLock(): bool
    {
        $redis = app('redis');
        if (!$redis || !$this->exclusive) {
            return true;
        }

        $cacheKey = 'teddyTask:lock:' . strtr(get_class($this), '\\', '');
        $redis->delete($cacheKey);
        return true;
    }

    protected function deliver()
    {
        $deliver = function (): void {
            app('swoole')->task($this);
        };

        if (defined('IN_SWOOLE') && IN_SWOOLE && $this->delay > 0) {
            return Timer::after($this->delay * 1000, $deliver);
        } else {
            $deliver();
        }
    }

    abstract protected function handle();
}
