<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 20:39:08 +0800
 */

namespace Teddy\Workerman\Processes;

use RuntimeException;
use Teddy\Application;
use Teddy\Interfaces\WebsocketHandlerInterface;
use Teddy\Traits\WebsocketAwareTrait;
use Teddy\Workerman\ProcessInterface as WorkermanProcessInterface;
use Teddy\Workerman\Websocket\Connection;
use Workerman\Connection\TcpConnection;

class WebsocketProcess extends AbstractProcess implements WorkermanProcessInterface
{
    use WebsocketAwareTrait;

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app, array $options = [])
    {
        $this->app = $app;

        $host = $options['host'] ?? '';
        $port = $options['port'] ?? 0;
        if ($host && $port) {
            $this->listen = 'websocket://'.$host.':'.$port;
        }

        $this->context = $options['context'] ?? [];
        $this->options = $options;

        $handler = $options['handler'] ?? null;
        if (!$handler || !is_subclass_of($handler, WebsocketHandlerInterface::class)) {
            throw new RuntimeException('Cannot found websocket handler.');
        }

        $this->handler = is_string($handler) ? new $handler() : $handler;
    }

    public function getName(): string
    {
        return 'websocket';
    }

    public function onConnect(TcpConnection $tcpConnection): void
    {
        $this->handleEvent('onConnect', new Connection($tcpConnection));
    }

    public function onMessage(TcpConnection $tcpConnection, string $data): void
    {
        $connection = new Connection($tcpConnection);
        if (!$this->handleEvent('onMessage', $connection, $data)) {
            $this->handleEvent('onClose', $connection);
        }
    }

    public function onClose(TcpConnection $tcpConnection): void
    {
        $this->handleEvent('onClose', new Connection($tcpConnection));
    }
}
