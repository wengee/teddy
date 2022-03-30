<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-20 19:58:47 +0800
 */

namespace Teddy;

use Exception;
use InvalidArgumentException;
use Teddy\Interfaces\TaskInterface;
use Teddy\Lock\Lock;

abstract class Task implements TaskInterface
{
    /** @var int */
    protected $timeout = 600;

    /** @var bool */
    protected $overlapped = false;

    /** @var mixed */
    protected $result = false;

    /** @var Teddy\Lock\Lock */
    protected $lock;

    /** @var null|string */
    protected $uniqueKey;

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

    public function finish()
    {
        return $this->result;
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
