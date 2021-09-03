<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Lock;

class Factory
{
    public function create(string $key, int $ttl = 600): Lock
    {
        return new Lock(new Key($key), $ttl);
    }
}
