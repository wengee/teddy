<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-25 01:29:02 +0800
 */
namespace Teddy\Swoole;

use Slim\Http\Response;
use Swoole\Http\Response as SwooleResponse;

class ResponseTransformer
{
    public static function mergeToSwoole(Response $slimResponse, SwooleResponse $response): SwooleResponse
    {
        $size = $slimResponse->getBody()->getSize();
        if ($size !== null) {
            $response->header('Content-Length', (string) $size);
        }

        $headers = $slimResponse->getHeaders();
        if (!empty($headers)) {
            foreach ($headers as $key => $headerArray) {
                if (stripos($key, 'Set-Cookie') !== 0) {
                    $response->header($key, implode('; ', $headerArray));
                }
            }
        }

        $cookies = method_exists($slimResponse, 'getCookies') ? $slimResponse->getCookies() : null;
        if (!empty($cookies)) {
            foreach ($cookies as $name => $cookie) {
                $response->cookie(
                    $name,
                    $cookie['value'],
                    $cookie['expire'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httponly']
                );
            }
        }

        $response->status($slimResponse->getStatusCode());
        if ($slimResponse->getBody()->getSize() > 0) {
            if ($slimResponse->getBody()->isSeekable()) {
                $slimResponse->getBody()->rewind();
            }

            $response->write($slimResponse->getBody()->getContents());
        }

        return $response;
    }
}
