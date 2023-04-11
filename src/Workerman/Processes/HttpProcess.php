<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-11 14:06:32 +0800
 */

namespace Teddy\Workerman\Processes;

use Exception;
use Teddy\Application;
use Teddy\Workerman\ProcessInterface as WorkermanProcessInterface;
use Teddy\Workerman\ResponseEmitter;
use Teddy\Workerman\ServerRequestFactory;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

class HttpProcess extends AbstractProcess implements WorkermanProcessInterface
{
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
            $this->listen = 'http://'.$host.':'.$port;
        }

        $this->context = $options['context'] ?? [];
        $this->options = $options;
    }

    public function getName(): string
    {
        return 'http';
    }

    public function onWorkerStart(Worker $worker): void
    {
    }

    public function onWorkerReload(Worker $worker): void
    {
    }

    public function onConnect(TcpConnection $connection): void
    {
    }

    public function onMessage(TcpConnection $connection, Request $request): void
    {
        try {
            $req = ServerRequestFactory::createServerRequestFromWorkerman($request, $connection);
            $res = $this->app->handle($req);
            (new ResponseEmitter($connection))->emit($res);
        } catch (Exception $e) {
            log_exception($e);
            $connection->send(new Response(500, [], 'Internal Server Error'));
        }

        $connection->close();
    }

    public function onClose(TcpConnection $connection): void
    {
    }

    public function onError(TcpConnection $connection, $code, $msg): void
    {
    }
}
