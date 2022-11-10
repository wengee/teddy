<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 20:44:24 +0800
 */

namespace Teddy\Workerman\Processes;

use Exception;
use RuntimeException;
use Teddy\Application;
use Teddy\Interfaces\WebsocketHandlerInterface;
use Teddy\Interfaces\WorkermanProcessInterface;
use Teddy\Workerman\Websocket\Connection;
use Workerman\Connection\TcpConnection;

class WebsocketProcess extends AbstractProcess implements WorkermanProcessInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var WebsocketHandlerInterface
     */
    protected $handler;

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

    public function onConnect(TcpConnection $connection): void
    {
        $this->handleEvent('onConnect', new Connection($connection));
    }

    public function onMessage(TcpConnection $connection, string $data): void
    {
        $this->handleEvent('onMessage', new Connection($connection), $data);
    }

    public function onClose(TcpConnection $connection): void
    {
        $this->handleEvent('onClose', new Connection($connection));
    }

    protected function handleEvent(string $method, ...$args): void
    {
        try {
            call_user_func([$this->handler, $method], ...$args);
        } catch (Exception $e) {
            log_exception($e);
        }
    }
}
