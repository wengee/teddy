<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-07 18:29:15 +0800
 */

namespace Teddy\Guzzle;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Handler\StreamHandler;
use Swoole\Coroutine\Http\Client as SwooleHttpClient;

class DefaultHandler
{
    protected static $handler;

    protected static $handlers = [
        'stream'    => StreamHandler::class,
        'curl'      => CurlHandler::class,
        'curlMulti' => CurlMultiHandler::class,
        'coroutine' => CoroutineHandler::class,
        'pool'      => PoolHandler::class,
    ];

    public static function set($handler = null)
    {
        if ($handler === null) {
            $handler = class_exists(SwooleHttpClient::class) ? 'coroutine' : 'curl';
        }

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
            self::set();
        }

        return self::$handler;
    }
}
