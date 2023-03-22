<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 15:40:53 +0800
 */

namespace Teddy;

class Hook
{
    protected static array $hookList = [];

    public static function add(string $name, callable $func): void
    {
        if (!isset(self::$hookList[$name])) {
            self::$hookList[$name] = [];
        }

        self::$hookList[$name][] = $func;
    }

    public static function run(string $name, ?array $params = []): void
    {
        if ($hooks = (self::$hookList[$name] ?? null)) {
            foreach ($hooks as $func) {
                call_user_func($func, $params);
            }
        }
    }
}
