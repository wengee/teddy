<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-06 15:16:45 +0800
 */

namespace Teddy\Lock;

class Factory
{
    public function create(string $key, int $ttl = 600): Lock
    {
        return new Lock(new Key($key), $ttl);
    }
}
