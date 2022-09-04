<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-04 16:15:57 +0800
 */

namespace Teddy;

final class Runtime
{
    public const SWOOLE    = 'swoole';
    public const WORKERMAN = 'workerman';
    public const UNKNOWN   = 'unknown';

    private static $initialized = false;

    private static $runtime;

    public static function set(?string $runtime): void
    {
        self::initialize();
        self::$runtime = $runtime ?: self::UNKNOWN;
    }

    public static function get(): string
    {
        self::initialize();

        return self::$runtime ?: self::UNKNOWN;
    }

    public static function is(string $runtime, bool $strict = false): ?bool
    {
        self::initialize();
        if (!$strict && self::UNKNOWN === self::$runtime) {
            return null;
        }

        return self::$runtime === $runtime;
    }

    public static function isSwoole(): bool
    {
        return self::is(self::SWOOLE, true);
    }

    public static function isWorkerman(): bool
    {
        return self::is(self::WORKERMAN, true);
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

            $swooleEnabled    = self::swooleEnabled();
            $workermanEnabled = self::workermanEnabled();
            if ($swooleEnabled && !$workermanEnabled) {
                $runtime = self::SWOOLE;
            } elseif ($workermanEnabled && !$swooleEnabled) {
                $runtime = self::WORKERMAN;
            }

            self::$runtime     = $runtime ?: self::UNKNOWN;
            self::$initialized = true;
        }
    }
}
