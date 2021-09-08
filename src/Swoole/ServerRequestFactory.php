<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-08 17:19:17 +0800
 */

namespace Teddy\Swoole;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;
use Slim\Psr7\Uri;
use Swoole\Http\Request as SwooleRequest;
use Teddy\Container\Container;

class ServerRequestFactory
{
    public static function createServerRequestFromSwoole(
        SwooleRequest $request
    ): ServerRequestInterface {
        $method        = $request->server['request_method'];
        $uri           = static::createUri($request);
        $headers       = static::createHeaders($request);
        $cookies       = (array) $request->cookie;
        $serverParams  = static::createServerParams($request);
        $body          = static::createBody($request);
        $uploadedFiles = static::createUploadFiles($request->files ?: null);

        $req = Container::getInstance()->getNew(ServerRequestInterface::class, [
            $method,
            $uri,
            $headers,
            $cookies,
            $serverParams,
            $body,
            $uploadedFiles,
        ]);

        return $req->withQueryParams($request->get ?? [])
            ->withParsedBody($request->post ?? [])
        ;
    }

    protected static function createUri(SwooleRequest $request): Uri
    {
        $isSecure = $request->header['https'] ?? null;
        $scheme   = (empty($isSecure) || 'off' === $isSecure) ? 'http' : 'https';

        $host = $request->header['host'];
        $port = null;
        $pos  = strpos($host, ':');
        if (false !== $pos) {
            $port = (int) substr($host, $pos + 1);
            $host = strstr($host, ':', true);
        }

        $path        = $request->server['request_uri'] ?? '/';
        $queryString = $request->server['query_string'] ?? '';
        $fragment    = '';
        $user        = '';
        $password    = '';

        return new Uri($scheme, $host, $port, $path, $queryString, $fragment, $user, $password);
    }

    protected static function createHeaders(SwooleRequest $request): Headers
    {
        return new Headers((array) $request->header, (array) $request->server);
    }

    protected static function createServerParams(SwooleRequest $request): array
    {
        $ret = [];
        foreach ($request->server as $key => $value) {
            $key       = str_replace('-', '_', strtoupper($key));
            $ret[$key] = $value;
        }

        foreach ($request->header as $key => $value) {
            $key       = str_replace('-', '_', strtoupper('HTTP_'.$key));
            $ret[$key] = $value;
        }

        return $ret;
    }

    protected static function createBody(SwooleRequest $request): StreamInterface
    {
        $stream = fopen('php://temp', 'w+');
        $body   = new Stream($stream);
        if (empty($request->rawContent())) {
            return $body;
        }

        $body->write($request->rawContent());
        $body->rewind();

        return $body;
    }

    protected static function createUploadFiles(?array $uploadedFiles): array
    {
        $parsed = [];
        if (empty($uploadedFiles)) {
            return $parsed;
        }

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
                    $uploadedFile['name'] ?? null,
                    $uploadedFile['type'] ?? null,
                    $uploadedFile['size'] ?? null,
                    $uploadedFile['error'],
                    true
                );
            } else {
                $subArray = [];
                foreach ($uploadedFile['error'] as $fileIdx => $error) {
                    // normalise subarray and re-parse to move the input's keyname up a level
                    $subArray[$fileIdx]['name']     = $uploadedFile['name'][$fileIdx];
                    $subArray[$fileIdx]['type']     = $uploadedFile['type'][$fileIdx];
                    $subArray[$fileIdx]['tmp_name'] = $uploadedFile['tmp_name'][$fileIdx];
                    $subArray[$fileIdx]['error']    = $uploadedFile['error'][$fileIdx];
                    $subArray[$fileIdx]['size']     = $uploadedFile['size'][$fileIdx];

                    $parsed[$field] = static::createUploadFiles($subArray);
                }
            }
        }

        return $parsed;
    }
}
