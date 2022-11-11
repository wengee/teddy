<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-11 16:16:06 +0800
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

    public function getId(): int
    {
        return $this->connection->id;
    }

    public function getRemoteIp(): string
    {
        return $this->connection->getRemoteIp();
    }

    public function getRemotePort(): int
    {
        return $this->connection->getRemotePort();
    }

    public function send($data, bool $raw = false): void
    {
        $this->connection->send($data, $raw);
    }

    public function close(): void
    {
        $this->connection->close();
    }
}
