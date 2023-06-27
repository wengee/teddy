<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-06-27 17:26:18 +0800
 */

namespace Teddy\Swoole;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;
use Teddy\Http\Response;

class ResponseEmitter
{
    protected $response;

    public function __construct(SwooleResponse $response)
    {
        $this->response = $response;
    }

    public function emit(ResponseInterface $res): void
    {
        $this->emitHeaders($res);
        $this->emitCookies($res);

        $statusCode = $res->getStatusCode();
        $this->response->status($statusCode);

        if (($res instanceof Response) && ($sendFile = $res->getSendFile())) {
            $this->response->sendfile($sendFile);

            return;
        }

        $body = $res->getBody();
        $size = (int) $body->getSize();
        if ($size > 0 && !in_array($statusCode, [204, 205, 304], true)) {
            if ($body->isSeekable()) {
                $body->rewind();
            }

            // $this->response->header('Content-Length', (string) $size);
            $this->response->end($body->getContents());

            return;
        }

        $this->response->end();
    }

    private function emitHeaders(ResponseInterface $res): void
    {
        foreach ($res->getHeaders() as $name => $values) {
            if (0 !== stripos($name, 'Set-Cookie')) {
                $this->response->header($name, implode('; ', $values));
            }
        }
    }

    private function emitCookies(ResponseInterface $res): void
    {
        $cookies = ($res instanceof Response) ? $res->getCookies() : null;

        if (!empty($cookies)) {
            foreach ($cookies as $name => $cookie) {
                $this->response->cookie(
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
    }
}
