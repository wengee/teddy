<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-11 16:17:10 +0800
 */

namespace Teddy\Swoole\Websocket;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Teddy\Interfaces\WebsocketConnectionInterface;

class Connection implements WebsocketConnectionInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $ws;

    public function __construct(Request $request, Response $ws)
    {
        $this->request = $request;
        $this->ws      = $ws;
    }

    public function getId(): int
    {
        return $this->request->fd;
    }

    public function getRemoteIp(): string
    {
        return $this->request->server['remote_addr'];
    }

    public function getRemotePort(): int
    {
        return (int) $this->request->server['remote_port'];
    }

    public function send($data, bool $raw = false): void
    {
        $this->ws->push($data, $raw ? WEBSOCKET_OPCODE_BINARY : WEBSOCKET_OPCODE_TEXT);
    }

    public function close(): void
    {
        $this->ws->close();

        throw new CloseException();
    }
}
