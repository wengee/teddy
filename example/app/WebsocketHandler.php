<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:53 +0800
 */

namespace App;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Teddy\Interfaces\WebsocketHandlerInterface;

class WebsocketHandler implements WebsocketHandlerInterface
{
    public function onOpen(Server $server, Request $request): void
    {
        $server->push($request->fd, "Welcome to Teddy App {$request->fd}");
    }

    public function onMessage(Server $server, Frame $frame): void
    {
        $server->push($frame->fd, date('Y-m-d H:i:s') . ' ' . $frame->fd);
    }

    public function onClose(Server $server, $fd, $reactorId): void
    {
    }
}
