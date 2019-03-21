<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-21 17:00:23 +0800
 */
namespace Teddy\Swoole;

use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\UploadedFile;
use Slim\Http\Uri;
use Swoole\Http\Request as SwooleRequest;

class RequestTransformer
{
    const DEFAULT_SCHEMA = 'http';

    protected static $requestClass;

    public static function getRequestClass(): string
    {
        if (!isset(static::$requestClass)) {
            static::$requestClass = app('settings')->get('requestClass', Request::class);
        }

        return static::$requestClass;
    }

    public static function toSlim(SwooleRequest $request): Request
    {
        $method = $request->server['request_method'];
        $uri = static::createUri($request);
        $headers = static::createHeaders($request);
        $cookies = (array) $request->cookie;
        $serverParams = static::createServerParams($request);
        $body = static::createBody($request);
        $uploadedFiles = static::createUploadFiles($request);

        $requestClass = static::getRequestClass();
        $slimRequest = new $requestClass($method, $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);

        if ($method === 'POST' && in_array($slimRequest->getMediaType(), ['application/x-www-form-urlencoded', 'multipart/form-data'])) {
            $slimRequest = $slimRequest->withParsedBody($request->post);
        }

        return $slimRequest;
    }

    protected static function createUri(SwooleRequest $request): Uri
    {
        $isSecure = $request->header['https'] ?? null;
        $scheme = (empty($isSecure) || $isSecure === 'off') ? 'http' : 'https';

        $host = $request->header['host'];
        $port = null;
        $pos = strpos($host, ':');
        if ($pos !== false) {
            $port = (int) substr($host, $pos + 1);
            $host = strstr($host, ':', true);
        }

        $path = $request->server['request_uri'] ?? '/';
        $queryString = $request->server['query_string'] ?? '';
        $fragment = '';
        $user = '';
        $password = '';

        return new Uri($scheme, $host, $port, $path, $queryString, $fragment, $user, $password);
    }

    protected static function createHeaders(SwooleRequest $request): Headers
    {
        return new Headers((array) $request->header);
    }

    protected static function createServerParams(SwooleRequest $request): array
    {
        $ret = [];
        foreach ($request->server as $key => $value) {
            $key = str_replace('-', '_', strtoupper($key));
            $ret[$key] = $value;
        }

        foreach ($request->header as $key => $value) {
            $key = str_replace('-', '_', strtoupper('HTTP_' . $key));
            $ret[$key] = $value;
        }

        return $ret;
    }

    protected static function createBody(SwooleRequest $request): Body
    {
        $stream = fopen('php://temp', 'w+');
        $body = new Body($stream);
        if (empty($request->rawContent())) {
            return $body;
        }

        $body->write($request->rawContent());
        $body->rewind();

        return $body;
    }

    protected static function createUploadFiles(SwooleRequest $request): array
    {
        $parsed = [];
        if (empty($request->files)) {
            return $parsed;
        }

        $uploadedFiles = (array) $request->files;
        foreach ($uploadedFiles as $field => $uploadedFile) {
            if (!isset($uploadedFile['error'])) {
                if (is_array($uploadedFile)) {
                    $parsed[$field] = static::createUploadFiles($uploadedFile);
                }
                continue;
            }

            $parsed[$field] = [];
            if (!is_array($uploadedFile['error'])) {
                $parsed[$field] = new UploadedFile(
                    $uploadedFile['tmp_name'],
                    isset($uploadedFile['name']) ? $uploadedFile['name'] : null,
                    isset($uploadedFile['type']) ? $uploadedFile['type'] : null,
                    isset($uploadedFile['size']) ? $uploadedFile['size'] : null,
                    $uploadedFile['error'],
                    true
                );
            } else {
                $subArray = [];
                foreach ($uploadedFile['error'] as $fileIdx => $error) {
                    // normalise subarray and re-parse to move the input's keyname up a level
                    $subArray[$fileIdx]['name'] = $uploadedFile['name'][$fileIdx];
                    $subArray[$fileIdx]['type'] = $uploadedFile['type'][$fileIdx];
                    $subArray[$fileIdx]['tmp_name'] = $uploadedFile['tmp_name'][$fileIdx];
                    $subArray[$fileIdx]['error'] = $uploadedFile['error'][$fileIdx];
                    $subArray[$fileIdx]['size'] = $uploadedFile['size'][$fileIdx];

                    $parsed[$field] = static::createUploadFiles($subArray);
                }
            }
        }

        return $parsed;
    }
}
