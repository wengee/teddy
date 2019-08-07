<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 15:08:41 +0800
 */

namespace App;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Teddy\Interfaces\WebsocketHandlerInterface;

class WebsocketHandler implements WebsocketHandlerInterface
{
    public function onOpen(Server $server, Request $request)
    {
        $server->push($request->fd, "Welcome to Teddy App {$request->fd}");
    }

    public function onMessage(Server $server, Frame $frame)
    {
        $server->push($frame->fd, date('Y-m-d H:i:s') . ' ' . $frame->fd);
    }

    public function onClose(Server $server, $fd, $reactorId)
    {
    }
}
