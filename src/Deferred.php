<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-11 17:24:22 +0800
 */

namespace Teddy;

use Swoole\Coroutine;

class Deferred
{
    /**
     * @var callable[]
     */
    protected static $funcList = [];

    public static function add(callable $callback): void
    {
        if (defined('IN_SWOOLE') && IN_SWOOLE) {
            Coroutine::defer($callback);
        } else {
            static::$funcList[] = $callback;
        }
    }

    public static function run(): void
    {
        if (!defined('IN_SWOOLE') || !IN_SWOOLE) {
            while ($func = array_pop(static::$funcList)) {
                safe_call($func);
            }
        }
    }

    public static function clear(): void
    {
        static::$funcList = [];
    }
}
