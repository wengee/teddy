<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-02 11:45:41 +0800
 */

namespace Teddy\Interfaces;

interface LockInterface
{
    public function acquire(): bool;

    public function refresh(?int $ttl = null): bool;

    public function isAcquired(): bool;

    public function release(): bool;
}
