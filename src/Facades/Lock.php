<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-12 11:00:57 +0800
 */

namespace Teddy\Facades;

/**
 * @method static \Teddy\Lock\Lock create(string $key, int $ttl = 600)
 */
class Lock extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'lock';
    }
}
