<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-19 15:00:09 +0800
 */

namespace Teddy\Scf;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Cookies;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

class ServerRequestFactory
{
    protected static $hostname;

    protected static $serverIp;

    public static function createRequest($event, $context): ServerRequestInterface
    {
        $method = $event->httpMethod ?: 'GET';
        $uri = static::createUri($event, $context);
        $headers = static::createHeaders($event, $context);
        $cookies = static::createCookies($event, $context);
        $serverParams = static::createServerParams($event, $context);
        $body = static::createBody($event, $context);

        $request = make('request', [
            $method,
            $uri,
            $headers,
            $cookies,
            $serverParams,
            $body,
        ]);

        return $request->withQueryParams((array) $event->queryString)
            ->withAttribute('event', $event)
            ->withAttribute('context', $context);
    }

    protected static function createUri($event, $context): Uri
    {
        $host = $event->headers ? $event->headers->host : '';
        $path = $event->path ?? '';
        $queryString = http_build_query($event->queryString);

        return new Uri('http', $host, null, $path, $queryString);
    }

    protected static function createHeaders($event, $context): Headers
    {
        return new Headers((array) $event->headers);
    }

    protected static function createCookies($event, $context): array
    {
        return Cookies::parseHeader($event->headers->cookie ?: '');
    }

    protected static function createServerParams($event, $context): array
    {
        if (!isset(static::$hostname)) {
            static::$hostname = gethostname();
            static::$serverIp = gethostbyname(static::$hostname);
        }

        $timestamp = microtime(true);
        $ret = [
            'HOSTNAME' => static::$hostname,
            'SERVER_ADDR' => static::$serverIp,
            'REMOTE_ADDR' => $event->requestContext->sourceIp ?: '',
            'REQUEST_TIME_FLOAT' => $timestamp,
            'REQUEST_TIME' => intval($timestamp),
        ];

        foreach ((array) $context as $key => $value) {
            $key = str_replace('-', '_', strtoupper($key));
            $ret[$key] = $value;
        }

        return $ret;
    }

    protected static function createBody($event, $context): StreamInterface
    {
        $stream = fopen('php://temp', 'w+');
        $body = new Stream($stream);
        if (empty($event->body)) {
            return $body;
        }

        $body->write((string) $event->body);
        $body->rewind();

        return $body;
    }
}
