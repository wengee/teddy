<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 17:54:29 +0800
 */

namespace Teddy\Swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Swoole\Process;
use Swoole\Server\Task as SwooleTask;
use Swoole\Websocket\Server as WebsocketServer;
use Teddy\Crontab\CrontabProcess;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\WebsocketHandlerInterface;
use Teddy\Task;
use Teddy\Utils;

defined('IN_SWOOLE') || define('IN_SWOOLE', true);

class Server
{
    protected $name = 'Teddy App';

    protected $swoole;

    protected $config;

    protected $enableCoroutine = true;

    protected $enableTaskCoroutine = true;

    protected $enableWebsocket = false;

    protected $websocketHandler;

    public function __construct($app, array $config = [])
    {
        $this->app = $app;

        $config += $this->getDefaultConfig();
        $this->init($config);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSwoole()
    {
        return $this->swoole;
    }

    public function start()
    {
        Utils::setProcessTitle('master process', $this->name);
        $this->swoole->start();
    }

    public function onStart(HttpServer $server)
    {
    }

    public function onWorkerStart(HttpServer $server, int $workerId)
    {
        if ($workerId >= $this->config['worker_num']) {
            $process = 'task worker';
        } else {
            $process = 'worker';
        }

        Utils::setProcessTitle($process, $this->name);
    }

    public function onRequest(Request $request, Response $response)
    {
        $this->app->run($request, $response);
    }

    public function onCoTask(HttpServer $server, SwooleTask $task)
    {
        $data = $task->data;

        if ($data instanceof Task) {
            $data->safeRun();
            if (method_exists($data, 'finish')) {
                $task->finish($data->finish());
            }
        }
    }

    public function onTask(HttpServer $server, int $taskId, int $srcWorkerId, $data)
    {
        if ($data instanceof Task) {
            $data->safeRun();
            if (method_exists($data, 'finish')) {
                return $data;
            }
        }
    }

    public function onFinish(HttpServer $server, int $taskId, $data)
    {
        if ($data instanceof Task) {
            $data->finish();
        }
    }

    protected function addProcess(ProcessInterface $process): void
    {
        $swoole = $this->swoole;
        $appName = $this->getName();
        $enableCoroutine = $this->enableCoroutine;
        $processHandler = function (Process $worker) use ($swoole, $appName, $process, $enableCoroutine) {
            $name = $process->getName() ?: 'custom';
            Utils::setProcessTitle($name, $appName);

            Process::signal(SIGUSR1, function ($signo) use ($name, $process, $worker, $swoole) {
                log_message('info', 'Reloading the process %s [pid=%d].', [$name, $worker->pid]);
                $process->onReload($swoole, $worker);
            });

            $runProcess = function () use ($name, $process, $swoole, $worker) {
                log_message('info', 'Run the process %s [pid=%d].', [$name, $worker->pid]);
                safe_call([$process, 'handle'], [$swoole, $worker]);
            };

            $enableCoroutine ? go($runProcess) : $runProcess();
        };

        $customProcess = new Process($processHandler, false, 0);
        $swoole->addProcess($customProcess);
    }

    protected function addProcesses(array $processes): void
    {
        foreach ($processes as $item) {
            $processCls = isset($item[0]) ? $item[0] : null;
            if (!$processCls || !class_exists($processCls)) {
                continue;
            }

            $args = isset($item[1]) ? $item[1] : [];
            if (!is_array($args)) {
                $args = [$args];
            }

            $this->addProcess(new $processCls(...$args));
        }
    }

    protected function bindWebSocketEvent(WebsocketHandlerInterface $websocketHandler): void
    {
        $eventHandler = function ($method, array $params) use ($websocketHandler) {
            safe_call([$websocketHandler, $method], $params);
        };

        $this->swoole->on('open', function (...$args) use ($eventHandler) {
            $eventHandler('onOpen', $args);
        });

        $this->swoole->on('message', function (...$args) use ($eventHandler) {
            $eventHandler('onMessage', $args);
        });

        $this->swoole->on('close', function (WebsocketServer $server, int $fd, int $reactorId) use ($eventHandler) {
            $clientInfo = $server->getClientInfo($fd);
            if (isset($clientInfo['websocket_status']) && $clientInfo['websocket_status'] === \WEBSOCKET_STATUS_FRAME) {
                $eventHandler('onClose', func_get_args());
            }
        });
    }

    protected function init(array $config): void
    {
        $this->config = $config;

        $this->name = array_pull($config, 'name', 'Teddy App');
        $this->enableCoroutine = array_get($config, 'enable_coroutine', true);
        $this->enableTaskCoroutine = $this->enableCoroutine && (version_compare(swoole_version(), '4.3.0') >= 0);
        $config['task_enable_coroutine'] = $this->enableTaskCoroutine;

        $crontab = array_pull($config, 'crontab');
        $processes = array_pull($config, 'processes');
        $enableWebsocket = array_pull($config, 'websocket.enable', false);
        $websocketHandler = array_pull($config, 'websocket.handler');

        $host = array_pull($config, 'host', '127.0.0.1');
        $port = array_pull($config, 'port', 9500);
        if ($enableWebsocket) {
            $this->swoole = new WebsocketServer($host, $port);
        } else {
            $this->swoole = new HttpServer($host, $port);
        }

        $this->swoole->set($config);
        $this->app->instance('server', $this);
        $this->app->instance('swoole', $this->swoole);

        $this->swoole->on('start', [$this, 'onStart']);
        $this->swoole->on('workerStart', [$this, 'onWorkerStart']);
        $this->swoole->on('request', [$this, 'onRequest']);
        if ($this->enableTaskCoroutine) {
            $this->swoole->on('task', [$this, 'onCoTask']);
        } else {
            $this->swoole->on('task', [$this, 'onTask']);
            $this->swoole->on('finish', [$this, 'onFinish']);
        }

        if ($enableWebsocket) {
            if (is_subclass_of($websocketHandler, WebsocketHandlerInterface::class)) {
                $websocketHandler = is_string($websocketHandler) ? new $websocketHandler :$websocketHandler;
                $this->bindWebSocketEvent($websocketHandler);
            }
        }

        if ($crontab && is_array($crontab)) {
            $this->addProcess(new CrontabProcess($crontab));
        }

        if ($processes && is_array($processes)) {
            $this->addProcesses($processes);
        }
    }

    protected function getDefaultConfig(): array
    {
        $cpuNum = swoole_cpu_num();
        return [
            'host' => '127.0.0.1',
            'port' => 9500,
            'enable_coroutine' => true,

            'reactor_num' => $cpuNum * 2,
            'worker_num' => $cpuNum * 2,
            'task_worker_num' => $cpuNum * 2,
            'dispatch_mode' => 1,
            'daemonize' => 0,
        ];
    }
}
