<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-18 13:38:14 +0800
 */

namespace Teddy\Workerman\Processes;

use Exception;
use Teddy\Abstracts\AbstractProcess;
use Teddy\Application;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Workerman\ResponseEmitter;
use Teddy\Workerman\ServerRequestFactory;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

class HttpProcess extends AbstractProcess implements ProcessInterface
{
    /** @var Application */
    protected $app;

    protected $name = 'http';

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

    public function onWorkerStart(Worker $worker): void
    {
        run_hook('workerman:http:beforeWorkerStart', ['worker' => $worker]);

        run_hook('workerman:http:afterWorkerStart', ['worker' => $worker]);
    }

    public function onWorkerReload(Worker $worker): void
    {
        run_hook('workerman:http:beforeWorkerReload', ['worker' => $worker]);

        run_hook('workerman:http:afterWorkerReload', ['worker' => $worker]);
    }

    public function onConnect(TcpConnection $connection): void
    {
        run_hook('workerman:http:beforeConnect', ['connection' => $connection]);

        run_hook('workerman:http:afterConnect', ['connection' => $connection]);
    }

    public function onMessage(TcpConnection $connection, Request $request): void
    {
        run_hook('workerman:http:beforeMessage', [
            'connection' => $connection,
            'request'    => $request,
        ]);

        try {
            $req = ServerRequestFactory::createServerRequestFromWorkerman($request);
            $res = $this->app->handle($req);
            (new ResponseEmitter($connection))->emit($res);
        } catch (Exception $e) {
            log_exception($e);
            $connection->send(new Response(500, [], 'Internal Server Error'));
        }

        run_hook('workerman:http:afterMessage', [
            'connection' => $connection,
            'request'    => $request,
        ]);
    }

    public function onClose(TcpConnection $connection): void
    {
        run_hook('workerman:http:beforeClose', ['connection' => $connection]);

        run_hook('workerman:http:afterClose', ['connection' => $connection]);
    }

    public function onError(TcpConnection $connection, $code, $msg): void
    {
        run_hook('workerman:http:beforeError', [
            'connection' => $connection,
            'code'       => $code,
            'msg'        => $msg,
        ]);

        run_hook('workerman:http:afterError', [
            'connection' => $connection,
            'code'       => $code,
            'msg'        => $msg,
        ]);
    }

    public function initialize(): Worker
    {
        $worker = new Worker('http://'.$this->host.':'.$this->port);

        $worker->name       = $this->name;
        $worker->count      = $this->count;
        $worker->reusePort  = $this->reusePort;
        $worker->reloadable = $this->reloadable;

        $worker->onWorkerStart  = [$this, 'onWorkerStart'];
        $worker->onWorkerReload = [$this, 'onWorkerReload'];
        $worker->onConnect      = [$this, 'onConnect'];
        $worker->onMessage      = [$this, 'onMessage'];
        $worker->onClose        = [$this, 'onClose'];
        $worker->onError        = [$this, 'onError'];

        $this->worker = $worker;

        return $worker;
    }
}
