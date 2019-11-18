<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-18 17:55:06 +0800
 */

namespace Teddy;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;

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
        $this->emitStatusLine($res);

        if ($sendFile = $res->getSendFile()) {
            $this->response->sendfile($sendFile);
            return;
        }

        if (!$this->isResponseEmpty($res)) {
            $this->emitBody($res);
        }

        $this->response->end();
    }

    private function emitHeaders(ResponseInterface $res): void
    {
        foreach ($res->getHeaders() as $name => $values) {
            if (stripos($name, 'Set-Cookie') !== 0) {
                $this->response->header($name, implode('; ', $values));
            }
        }
    }

    private function emitCookies(ResponseInterface $res): void
    {
        $cookies = method_exists($res, 'getCookies') ? $res->getCookies() : null;

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

    private function emitStatusLine(ResponseInterface $res): void
    {
        $this->response->status($res->getStatusCode());
    }

    private function emitBody(ResponseInterface $res): void
    {
        $body = $res->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        $size = $body->getSize();
        if ($size > 0) {
            $this->response->header('Content-Length', (string) $size);
            $this->response->write($body->getContents());
        }
    }

    private function isResponseEmpty(ResponseInterface $res): bool
    {
        $contents = (string) $res->getBody();
        return !strlen($contents) || in_array($res->getStatusCode(), [204, 205, 304], true);
    }
}
