<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 23:55:22 +0800
 */

namespace Teddy\Swoole\Processes;

use Exception;
use Swoole\Coroutine\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Process;
use Teddy\Application;
use Teddy\Interfaces\SwooleProcessInterface;
use Teddy\Swoole\ResponseEmitter;
use Teddy\Swoole\ServerRequestFactory;

class HttpProcess extends AbstractProcess implements SwooleProcessInterface
{
    protected $name = 'http process';

    protected $enableCoroutine = true;

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app, array $options = [])
    {
        $this->app       = $app;
        $this->count     = $options['count'] ?? 1;
        $this->host      = $options['host'] ?? '';
        $this->port      = $options['port'] ?? 0;
        $this->useSSL    = $options['ssl'] ?? false;
        $this->reusePort = $options['reusePort'] ?? true;
        $this->options   = $options['options'] ?? [];
    }

    public function handle(): void
    {
        $server = new Server($this->host, $this->port, $this->useSSL, $this->reusePort);
        $server->set($this->options);

        Process::signal(SIGTERM, function () use ($server): void {
            $server->shutdown();
        });

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

        $server->start();
    }
}
