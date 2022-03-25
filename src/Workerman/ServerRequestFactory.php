<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-14 16:00:45 +0800
 */

namespace Teddy\Workerman;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;
use Slim\Psr7\Uri;
use Teddy\Container\Container;
use Workerman\Protocols\Http\Request as WorkermanRequest;

class ServerRequestFactory
{
    public static function createServerRequestFromWorkerman(WorkermanRequest $request): ServerRequestInterface
    {
        $method        = $request->method();
        $uri           = static::createUri($request);
        $headers       = static::createHeaders($request);
        $cookies       = (array) $request->cookie;
        $serverParams  = static::createServerParams($request);
        $body          = static::createBody($request);
        $uploadedFiles = static::createUploadFiles($request->file());

        $req = Container::getInstance()->getNew(ServerRequestInterface::class, [
            $method,
            $uri,
            $headers,
            $cookies,
            $serverParams,
            $body,
            $uploadedFiles,
        ]);

        return $req->withQueryParams($request->get())
            ->withParsedBody($request->post())
        ;
    }

    protected static function createUri(WorkermanRequest $request): Uri
    {
        $isSecure = $request->header('https');
        $scheme   = (empty($isSecure) || 'off' === $isSecure) ? 'http' : 'https';

        $host = $request->host();
        $port = null;
        $pos  = strpos($host, ':');
        if (false !== $pos) {
            $port = (int) substr($host, $pos + 1);
            $host = strstr($host, ':', true);
        }

        $path        = $request->path();
        $queryString = $request->queryString();
        $fragment    = '';
        $user        = '';
        $password    = '';

        return new Uri($scheme, $host, $port, $path, $queryString, $fragment, $user, $password);
    }

    protected static function createHeaders(WorkermanRequest $request): Headers
    {
        return new Headers($request->header());
    }

    protected static function createServerParams(WorkermanRequest $request): array
    {
        $ret = [];
        foreach ($request->header() as $key => $value) {
            $key       = str_replace('-', '_', strtoupper('HTTP_'.$key));
            $ret[$key] = $value;
        }

        return $ret;
    }

    protected static function createBody(WorkermanRequest $request): StreamInterface
    {
        $resource = fopen('php://temp', 'rw+');

        fwrite($resource, (string) $request->rawBody());
        rewind($resource);

        return new Stream($resource);
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
