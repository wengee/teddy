<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Swoole;

use BadMethodCallException;
use Swoole\Coroutine as SwooleCoroutine;

class Coroutine
{
    public static function __callStatic($name, $arguments)
    {
        if (!method_exists(SwooleCoroutine::class, $name)) {
            throw new BadMethodCallException(sprintf('Call to undefined method %s.', $name));
        }
        return SwooleCoroutine::$name(...$arguments);
    }

    public static function id(): int
    {
        return SwooleCoroutine::getCid();
    }

    public static function parentId(): int
    {
        return SwooleCoroutine::getPcid();
    }

    public static function inCoroutine(): bool
    {
        return self::id() > 0;
    }
}
