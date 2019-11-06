<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-06 14:12:22 +0800
 */

namespace Teddy\Interfaces;

interface LockInterface
{
    public function acquire(): bool;

    public function refresh(float $ttl = null): bool;

    public function isAcquired(): bool;

    public function release(): bool;
}
