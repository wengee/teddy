<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-14 16:20:46 +0800
 */

namespace Teddy\Workerman;

use Psr\Http\Message\ResponseInterface;
use Teddy\Http\Response;
use Teddy\Interfaces\CookieAwareInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response as HttpResponse;

class ResponseEmitter
{
    /** @var TcpConnection */
    protected $connection;

    public function __construct(TcpConnection $connection)
    {
        $this->connection = $connection;
    }

    public function emit(ResponseInterface $res): void
    {
        $response = new HttpResponse(
            $res->getStatusCode(),
            $res->getHeaders()
        );

        if ($res instanceof CookieAwareInterface) {
            foreach ($res->getCookies() as $name => $cookie) {
                $response->cookie(
                    $name,
                    $cookie['value'],
                    $cookie['maxAge'],
                    $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httpOnly'],
                $cookie['sameSite']
                );
            }
        }

        if (($res instanceof Response) && ($sendFile = $res->getSendFile())) {
            $response->withFile($sendFile);
        } else {
            $body = $res->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }

            $size = $body->getSize();
            if ($size > 0) {
                $response->header('Content-Length', (string) $size);
                $response->withBody($body->getContents());
            }
        }

        $this->connection->send($response);
    }
}
