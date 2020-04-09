<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-04-09 10:15:18 +0800
 */

namespace Teddy\Swoole;

use BadMethodCallException;
use RuntimeException;
use Swoole\Coroutine as SwooleCoroutine;

class Coroutine
{
    public static function __callStatic($name, $arguments)
    {
        if (!class_exists(SwooleCoroutine::class)) {
            throw new RuntimeException('Swoole extension is not found.');
        }

        if (!method_exists(SwooleCoroutine::class, $name)) {
            throw new BadMethodCallException(sprintf('Call to undefined method %s.', $name));
        }

        return SwooleCoroutine::$name(...$arguments);
    }

    public static function id(): int
    {
        if (!self::inCoroutine()) {
            return 0;
        }

        return SwooleCoroutine::getCid();
    }

    public static function parentId(): int
    {
        if (!self::inCoroutine()) {
            return 0;
        }

        return SwooleCoroutine::getPcid();
    }

    public static function inCoroutine(): bool
    {
        return class_exists(SwooleCoroutine::class) && self::id() > 0;
    }
}
