<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 17:43:57 +0800
 */

namespace Teddy\Workerman\Websocket;

use Teddy\Interfaces\WebsocketConnectionInterface;
use Workerman\Connection\TcpConnection;

class Connection implements WebsocketConnectionInterface
{
    /**
     * @var TcpConnection
     */
    protected $connection;

    public function __construct(TcpConnection $connection)
    {
        $this->connection = $connection;
    }

    public function send($data): void
    {
        $this->connection->send($data);
    }

    public function close(): void
    {
        $this->connection->close();
    }
}
