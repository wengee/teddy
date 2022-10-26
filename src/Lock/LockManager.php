<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-24 11:34:23 +0800
 */

namespace Teddy\Lock;

class LockManager
{
    public function create(string $key, int $ttl = 600): Lock
    {
        return new Lock(new Key($key), $ttl);
    }
}
