<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Interfaces;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

interface WebsocketHandlerInterface
{
    public function onOpen(Server $server, Request $request);

    public function onMessage(Server $server, Frame $frame);

    public function onClose(Server $server, $fd, $reactorId);
}
