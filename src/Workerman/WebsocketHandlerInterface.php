<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Workerman;

use Workerman\Connection\TcpConnection;
use Workerman\Worker;

interface WebsocketHandlerInterface
{
    public function onWorkerStart(Worker $worker);

    public function onWorkerReload(Worker $worker);

    public function onConnect(TcpConnection $connection);

    public function onMessage(TcpConnection $connection, string $data);

    public function onClose(TcpConnection $connection);

    public function onError(TcpConnection $connection, $code, $msg);
}
