<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-11-16 15:21:11 +0800
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
    protected $timeout = 600;

    /**
     * @var bool
     */
    protected $overlapped = false;

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

    public function getDelay(): int
    {
        return (int) $this->delay;
    }

    public function delay(int $delay): self
    {
        if ($delay <= 0) {
            throw new InvalidArgumentException('The delay must be greater than 0');
        }

        $this->delay = $delay;

        return $this;
    }

    public function waitTimeout(float $waitTimeout = 3.0): self
    {
        if ($waitTimeout <= 0) {
            throw new InvalidArgumentException('The waitTimeout must be greater than 0');
        }

        $this->waitTimeout = $waitTimeout;

        return $this;
    }

    public function timeout(int $timeout): self
    {
        if ($timeout <= 0) {
            throw new InvalidArgumentException('The timeout must be greater than 0');
        }

        $this->timeout = $timeout;

        return $this;
    }

    public function withOverlapping(bool $overlapped = true): self
    {
        $this->overlapped = $overlapped;

        return $this;
    }

    public function isWaiting(): bool
    {
        return $this->waitTimeout > 0;
    }

    public function send(bool $toQueue = false)
    {
        if ($this->isWaiting()) {
            return $this->sendAndWait();
        }

        if ($toQueue) {
            $this->sendToQueue();
        } else {
            $this->sendToLocal();
        }
    }

    public function queue(): void
    {
        $this->sendToQueue();
    }

    public function finish()
    {
        return $this->result;
    }

    public function safeRun(): void
    {
        safe_call([$this, 'run']);
    }

    public function run()
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

    public function isRunning(): bool
    {
        if ($this->overlapped) {
            return false;
        }

        return $this->getLock()->isAcquired();
    }

    public function setUniqueKey(?string $uniqueKey): self
    {
        $this->uniqueKey = $uniqueKey;

        return $this;
    }

    protected function sendAndWait(float $waitTimeout = 3.0)
    {
        $this->waitTimeout($waitTimeout);
        $ret = app('swoole')->taskCo([$this], $this->waitTimeout);

        return ($ret && isset($ret[0])) ? $ret[0] : null;
    }

    protected function sendToLocal(): void
    {
        static::deliver($this);
    }

    protected function sendToQueue(): void
    {
        $queue = app('queue');
        if (!$queue) {
            $this->sendToLocal();
        } else {
            $queue->push($this);
        }
    }

    protected function getLock(): Lock
    {
        if (!isset($this->lock)) {
            $lockKey    = 'task:'.($this->uniqueKey ?: strtr(get_class($this), '\\', '_'));
            $this->lock = app('lock')->create($lockKey, $this->timeout);
        }

        return $this->lock;
    }

    protected function tryLock(): bool
    {
        if ($this->overlapped) {
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
        if ($this->overlapped) {
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
