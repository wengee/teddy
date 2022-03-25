<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-25 14:39:55 +0800
 */

namespace Teddy\Workerman\Processes;

use Teddy\Abstracts\AbstractProcess;
use Teddy\Application;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Workerman\WebsocketHandlerInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class WebsocketProcess extends AbstractProcess implements ProcessInterface
{
    /** @var Application */
    protected $app;

    protected $name = 'websocket';

    /** @var null|WebsocketHandlerInterface */
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
        if ($handler && is_subclass_of($handler, WebsocketHandlerInterface::class)) {
            $this->handler = is_string($handler) ? new $handler() : $handler;
        }
    }

    public function onWorkerStart(Worker $worker): void
    {
        run_hook('workerman:websocket:beforeWorkerStart', ['worker' => $worker]);

        run_hook('workerman:websocket:afterWorkerStart', ['worker' => $worker]);
    }

    public function onWorkerReload(Worker $worker): void
    {
        run_hook('workerman:websocket:beforeWorkerReload', ['worker' => $worker]);

        run_hook('workerman:websocket:afterWorkerReload', ['worker' => $worker]);
    }

    public function onConnect(TcpConnection $connection): void
    {
        run_hook('workerman:websocket:beforeConnect', ['connection' => $connection]);

        $this->handleEvent('onConnect', $connection);

        run_hook('workerman:websocket:afterConnect', ['connection' => $connection]);
    }

    public function onMessage(TcpConnection $connection, string $data): void
    {
        run_hook('workerman:websocket:beforeMessage', [
            'connection' => $connection,
            'data'       => $data,
        ]);

        $this->handleEvent('onMessage', $connection, $data);

        run_hook('workerman:websocket:afterMessage', [
            'connection' => $connection,
            'data'       => $data,
        ]);
    }

    public function onClose(TcpConnection $connection): void
    {
        run_hook('workerman:websocket:beforeClose', ['connection' => $connection]);

        $this->handleEvent('onClose', $connection);

        run_hook('workerman:websocket:afterClose', ['connection' => $connection]);
    }

    public function onError(TcpConnection $connection, $code, $msg): void
    {
        run_hook('workerman:websocket:beforeError', [
            'connection' => $connection,
            'code'       => $code,
            'msg'        => $msg,
        ]);

        $this->handleEvent('onError', $connection, $code, $msg);

        run_hook('workerman:websocket:afterError', [
            'connection' => $connection,
            'code'       => $code,
            'msg'        => $msg,
        ]);
    }

    protected function handleEvent(string $method, ...$args): void
    {
        if ($this->handler) {
            call_user_func([$this->handler, $method], ...$args);
        }
    }
}
