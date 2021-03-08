<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-08 10:29:39 +0800
 */

namespace Teddy;

use Exception;
use InvalidArgumentException;
use Swoole\Timer;
use Teddy\Lock\Lock;

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
     * @var int
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

    /**
     * @var Teddy\Lock\Lock
     */
    protected $lock;

    /**
     * @var null|string
     */
    protected $uniqueKey;

    final public static function deliver(Task $task): void
    {
        if (app('swoole')->taskworker) {
            $task->safeRun();

            return;
        }

        $deliver = function () use ($task): void {
            app('swoole')->task($task);
        };

        if ($task->getDelay() > 0) {
            Timer::after($task->getDelay(), $deliver);
        } else {
            $deliver();
        }
    }

    final public function getDelay(): int
    {
        return (int) $this->delay;
    }

    final public function delay(int $delay): self
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
            $this->exclusive     = true;
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

        return ($ret && isset($ret[0])) ? $ret[0] : null;
    }

    final public function send()
    {
        if ($this->isWaiting()) {
            return $this->sendWait();
        }

        static::deliver($this);
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

        return false;
    }

    final public function isRunning(): bool
    {
        if (!$this->exclusive) {
            return false;
        }

        return $this->getLock()->isAcquired();
    }

    final public function setUniqueKey(?string $uniqueKey): self
    {
        $this->uniqueKey = $uniqueKey;

        return $this;
    }

    protected function getLock(): Lock
    {
        if (!isset($this->lock)) {
            $lockKey    = 'task:'.($this->uniqueKey ?: strtr(get_class($this), '\\', '_'));
            $this->lock = app('lock')->create($lockKey, $this->executionTime);
        }

        return $this->lock;
    }

    protected function tryLock(): bool
    {
        if (!$this->exclusive) {
            return true;
        }

        try {
            return $this->getLock()->acquire();
        } catch (Exception $e) {
            log_exception($e);

            return false;
        }
    }

    protected function unLock(): bool
    {
        if (!$this->exclusive) {
            return true;
        }

        try {
            return $this->getLock()->release();
        } catch (Exception $e) {
            log_exception($e);

            return false;
        }
    }

    abstract protected function handle();
}
