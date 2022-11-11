<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-11 21:30:25 +0800
 */

namespace Teddy\Swoole;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;
use Teddy\Interfaces\CookieAwareInterface;
use Teddy\Interfaces\FileResponseInterface;

class ResponseEmitter
{
    /**
     * @var SwooleResponse
     */
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

        if (($res instanceof FileResponseInterface) && ($sendFile = $res->getSendFile())) {
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
            if (0 !== stripos($name, 'Set-Cookie')) {
                $this->response->header($name, implode('; ', $values));
            }
        }
    }

    private function emitCookies(ResponseInterface $res): void
    {
        $now     = time();
        $cookies = ($res instanceof CookieAwareInterface) ? $res->getCookies() : null;

        if (!empty($cookies)) {
            foreach ($cookies as $name => $cookie) {
                $this->response->cookie(
                    $name,
                    $cookie['value'],
                    $now + $cookie['maxAge'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httpOnly']
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
            // $this->response->header('Content-Length', (string) $size);
            $this->response->write($body->getContents());
        }
    }

    private function isResponseEmpty(ResponseInterface $res): bool
    {
        $contents = (string) $res->getBody();

        return !strlen($contents) || in_array($res->getStatusCode(), [204, 205, 304], true);
    }
}
