<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-22 11:12:10 +0800
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

    /** @var mixed */
    protected $result = false;

    /** @var null|Teddy\Lock\Lock */
    protected $lock;

    /** @var null|bool|string */
    protected $uniqueId = true;

    public function timeout(int $timeout): self
    {
        if ($timeout <= 0) {
            throw new InvalidArgumentException('The timeout must be greater than 0');
        }

        $this->timeout = $timeout;

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
        if (!($lock = $this->getLock())) {
            return false;
        }

        return $lock->isAcquired();
    }

    protected function getUniqueId(): ?string
    {
        if (true === $this->uniqueId) {
            $this->uniqueId = strtolower(strtr(get_class($this), '\\', '_'));
        }

        return $this->uniqueId ?: null;
    }

    protected function getLock(): ?Lock
    {
        if (!($uniqueId = $this->getUniqueId())) {
            return null;
        }

        if (null === $this->lock) {
            $this->lock = app('lock')->create('task:'.$uniqueId, $this->timeout);
        }

        return $this->lock;
    }

    protected function tryLock(): bool
    {
        if (!($lock = $this->getLock())) {
            return true;
        }

        try {
            return $lock->acquire();
        } catch (Exception $e) {
            log_exception($e);

            return false;
        }
    }

    protected function unLock(): bool
    {
        if (!($lock = $this->getLock())) {
            return true;
        }

        try {
            return $lock->release();
        } catch (Exception $e) {
            log_exception($e);

            return false;
        }
    }

    abstract protected function handle();
}
