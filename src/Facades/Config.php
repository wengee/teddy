<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-12 10:58:46 +0800
 */

namespace Teddy\Facades;

/**
 * @method static bool has(string $key)
 * @method static mixed get(array|string $key, mixed $default = null)
 * @method static array getMany(array $keys)
 * @method static void set(array|string $key, mixed $value)
 * @method static void prepend(string $key, mixed $value)
 * @method static void push(string $key, mixed $value)
 * @method static array all()
 */
class Config extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'config';
    }
}
