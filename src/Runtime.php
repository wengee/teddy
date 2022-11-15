<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-15 20:45:28 +0800
 */

namespace Teddy;

final class Runtime
{
    public const SWOOLE    = 'swoole';
    public const WORKERMAN = 'workerman';

    public const Swoole    = 'swoole';
    public const Workerman = 'workerman';

    private static $initialized = false;

    private static $runtime;

    public static function set(string $runtime): void
    {
        self::initialize();
        self::$runtime = $runtime;
    }

    public static function get(): string
    {
        self::initialize();

        return self::$runtime;
    }

    public static function is(string $runtime): ?bool
    {
        self::initialize();

        return self::$runtime === $runtime;
    }

    public static function isSwoole(): bool
    {
        return self::is(self::SWOOLE);
    }

    public static function isWorkerman(): bool
    {
        return self::is(self::WORKERMAN);
    }

    public static function swooleEnabled(): bool
    {
        return (bool) extension_loaded('swoole') && class_exists('\\Swoole\\Http\\Server');
    }

    public static function workermanEnabled(): bool
    {
        return (bool) class_exists('\\Workerman\\Worker');
    }

    private static function initialize(): void
    {
        if (!self::$initialized) {
            $runtime = env('TEDDY_RUNTIME');

            if (!$runtime) {
                if (self::swooleEnabled()) {
                    $runtime = self::SWOOLE;
                } else {
                    $runtime = self::WORKERMAN;
                }
            }

            self::$runtime     = $runtime;
            self::$initialized = true;
        }
    }
}
