<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-04 15:47:09 +0800
 */

namespace Teddy;

use Swoole\Timer as SwooleTimer;
use Workerman\Timer as WorkermanTimer;

final class Timer
{
    /**
     * @param int $ms 毫秒
     */
    public static function tick(int $ms, callable $callback): int
    {
        if (Runtime::isSwoole()) {
            return SwooleTimer::tick($ms, $callback);
        }

        if (Runtime::isWorkerman()) {
            return WorkermanTimer::add($ms / 1000, $callback);
        }

        return 0;
    }

    /**
     * @param int $ms 毫秒
     */
    public static function after(int $ms, callable $callback): int
    {
        if (Runtime::isSwoole()) {
            return SwooleTimer::after($ms, $callback);
        }

        if (Runtime::isWorkerman()) {
            return WorkermanTimer::add($ms / 1000, $callback, [], false);
        }

        return 0;
    }
}
