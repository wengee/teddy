<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-17 15:10:40 +0800
 */
namespace SlimExtra\Qcloud;

use Slim\Http\Environment;
use Slim\Http\Request;

class RequestTransformer
{
    protected static $hostname;

    protected static $serverIp;

    protected static $requestClass;

    public static function getRequestClass(): string
    {
        if (!isset(static::$requestClass)) {
            static::$requestClass = app('settings')->get('requestClass', Request::class);
        }

        return static::$requestClass;
    }

    public static function create($event, $context): Request
    {
        if (!isset(static::$hostname)) {
            static::$hostname = gethostname();
            static::$serverIp = gethostbyname(static::$hostname);
        }

        $timestamp = microtime(true);
        $method = $event->httpMethod ?: 'GET';
        $queryString = urldecode(http_build_query($event->queryString ?: ''));
        $url = $event->path ?: '/';
        if ($queryString) {
            $url .= '?' . $queryString;
        }

        $data = [
            'HOSTNAME' => static::$hostname,
            'SERVER_ADDR' => static::$serverIp,
            'REMOTE_ADDR' => $event->requestContext->sourceIp ?: '',
            'REQUEST_URI' => $url,
            'REQUEST_METHOD' => $method,
            'QUERY_STRING' => $queryString,
            'REQUEST_TIME_FLOAT' => $timestamp,
            'REQUEST_TIME' => intval($timestamp),
        ];

        $headers = (array) $event->headers ?: [];
        foreach ($headers as $key => $value) {
            $key = str_replace('-', '_', strtoupper('HTTP_' . $key));
            $data[$key] = $value;
        }

        foreach ((array) $context as $key => $value) {
            $key = str_replace('-', '_', strtoupper($key));
            $data[$key] = $value;
        }

        $requestClass = static::getRequestClass();
        $request = $requestClass::createFromEnvironment(new Environment($data));

        // if ($method === 'POST' && in_array($request->getMediaType(), ['application/x-www-form-urlencoded', 'multipart/form-data'])) {
        //     $request = $request->withParsedBody($request->post);
        // }

        $request = $request->withAttribute('event', $event)
                           ->withAttribute('context', $context);

        return $request;
    }
}
