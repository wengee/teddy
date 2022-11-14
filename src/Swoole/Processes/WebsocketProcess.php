<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 20:40:35 +0800
 */

namespace Teddy\Swoole\Processes;

use Exception;
use RuntimeException;
use Swoole\Coroutine\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Process;
use Swoole\WebSocket\CloseFrame;
use Teddy\Application;
use Teddy\Interfaces\WebsocketHandlerInterface;
use Teddy\Swoole\ProcessInterface as SwooleProcessInterface;
use Teddy\Swoole\ResponseEmitter;
use Teddy\Swoole\ServerRequestFactory;
use Teddy\Swoole\Websocket\Connection;
use Teddy\Traits\WebsocketAwareTrait;

class WebsocketProcess extends AbstractProcess implements SwooleProcessInterface
{
    use WebsocketAwareTrait;

    protected $name = 'websocket';

    protected $enableCoroutine = true;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $httpProcess = false;

    public function __construct(Application $app, array $options = [])
    {
        $this->app         = $app;
        $this->count       = $options['count'] ?? 1;
        $this->host        = $options['host'] ?? '';
        $this->port        = $options['port'] ?? 0;
        $this->useSSL      = $options['ssl'] ?? false;
        $this->reusePort   = $options['reusePort'] ?? true;
        $this->path        = $options['path'] ?? null;
        $this->httpProcess = $options['httpProcess'] ?? false;
        $this->options     = $options['options'] ?? [];

        $handler = $options['handler'] ?? null;
        if (!$handler || !is_subclass_of($handler, WebsocketHandlerInterface::class)) {
            throw new RuntimeException('Cannot found websocket handler.');
        }

        $this->handler = is_string($handler) ? new $handler() : $handler;
    }

    public function handle(int $workerId): void
    {
        if (!$this->host || !$this->port) {
            throw new RuntimeException('Invalid parameter. (host or port)');
        }

        $server = new Server($this->host, $this->port, $this->useSSL, $this->reusePort);
        $server->set($this->options);

        Process::signal(SIGTERM, function () use ($server): void {
            $server->shutdown();
        });

        if (null === $this->path) {
            $this->path = $this->httpProcess ? '/websocket' : '/';
        }

        $server->handle($this->path, function (Request $request, Response $ws): void {
            $ws->upgrade();
            $connection = new Connection($request, $ws);
            $this->handleEvent('onConnect', $connection);

            while (true) {
                $frame = $ws->recv();
                if (false === $frame || CloseFrame::class === get_class($frame)) {
                    $ws->close();
                    $this->handleEvent('onClose', $connection);

                    break;
                }

                if (!$this->handleEvent('onMessage', $connection, $frame->data)) {
                    $this->handleEvent('onClose', $connection);

                    break;
                }
            }
        });

        if ($this->httpProcess) {
            $server->handle('/', function (Request $request, Response $response): void {
                try {
                    $req = ServerRequestFactory::createServerRequestFromSwoole($request);
                    $res = $this->app->handle($req);
                    (new ResponseEmitter($response))->emit($res);
                } catch (Exception $e) {
                    log_exception($e);
                    $response->detach();

                    $response = Response::create($request->fd);
                    $response->status(500);
                    $response->end('Internal Server Error');
                }
            });
        }

        $server->start();
    }
}
