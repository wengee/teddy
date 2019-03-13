<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-15 11:36:17 +0800
 */
namespace Teddy\Swoole;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

interface WebsocketHandlerInterface
{
    public function onOpen(Server $server, Request $request);

    public function onMessage(Server $server, Frame $frame);

    public function onClose(Server $server, $fd, $reactorId);
}
