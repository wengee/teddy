<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-22 14:34:55 +0800
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

    /** @var null|Teddy\Lock\Lock */
    protected $lock;

    /** @var null|bool|string */
    protected $uniqueId = true;

    public function timeout(int $timeout): self
    {
        if ($timeout <= 0) {
            throw new InvalidArgumentException('Timeout must be greater than 0');
        }

        $this->timeout = $timeout;

        return $this;
    }

    public function run(): void
    {
        if ($this->tryLock()) {
            try {
                $this->handle();
            } catch (Exception $e) {
                $this->unLock();

                throw $e;
            }

            $this->unLock();
        } else {
            throw new Exception('The same task is running.');
        }
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
