<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-07 18:29:15 +0800
 */

namespace Teddy\Guzzle;

class DefaultHandler
{
    protected static $handler;

    protected static $handlers = [
        'stream'    => \GuzzleHttp\Handler\StreamHandler::class,
        'curl'      => \GuzzleHttp\Handler\CurlHandler::class,
        'curlMulti' => \GuzzleHttp\Handler\CurlMultiHandler::class,
        'coroutine' => CoroutineHandler::class,
        'pool'      => PoolHandler::class,
    ];

    public static function set($handler): void
    {
        if (is_string($handler)) {
            $handler = self::$handlers[$handler] ?? $handler;
            if (class_exists($handler)) {
                self::$handler = new $handler;
            }
        } else {
            self::$handler = $handler;
        }
    }

    public static function get()
    {
        if (!isset(self::$handler)) {
            self::set('coroutine');
        }

        return self::$handler;
    }
}
