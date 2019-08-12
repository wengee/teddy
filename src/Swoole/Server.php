<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-12 18:05:10 +0800
 */

namespace Teddy\Swoole;

use Exception;
use Swoole\Coroutine;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Swoole\Process;
use Swoole\Runtime;
use Swoole\Server\Task as SwooleTask;
use Swoole\Websocket\Server as WebsocketServer;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\WebsocketHandlerInterface;
use Teddy\Schedule\ScheduleProcess;
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

    protected $coroutineFlags = SWOOLE_HOOK_ALL;

    protected $enableWebsocket = false;

    protected $websocketHandler;

    public function __construct($app, array $config = [])
    {
        $this->app = $app;
        $this->name = $app->getName();

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
            if ($this->enableTaskCoroutine) {
                Runtime::enableCoroutine(true, $this->coroutineFlags);
            } else {
                Runtime::enableCoroutine(false);
            }
        } else {
            $process = 'worker';
            if ($this->enableCoroutine) {
                Runtime::enableCoroutine(true, $this->coroutineFlags);
            } else {
                Runtime::enableCoroutine(false);
            }
        }

        Utils::setProcessTitle($process, $this->name);
    }

    public function onRequest(Request $request, Response $response)
    {
        try {
            $this->app->run($request, $response);
        } catch (Exception $e) {
            log_exception($e);
            $response->status(500);
            $response->end('Internal Server Error');
        }
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

    public function stats(): array
    {
        $serverStats = $this->swoole->stats();
        $coroutineStats = Coroutine::stats();
        return [
            'hostname'                  => gethostname(),
            'currentWorkPid'            => getmypid(),
            'phpVersion'                => PHP_VERSION,
            'swooleVersion'             => SWOOLE_VERSION,
            'server' => [
                'startTime'             => $serverStats['start_time'] ?? null,
                'connectionNum'         => $serverStats['connection_num'] ?? null,
                'acceptCount'           => $serverStats['accept_count'] ?? null,
                'closeCount'            => $serverStats['close_count'] ?? null,
                'workerNum'             => $serverStats['worker_num'] ?? null,
                'idleWorkerNum'         => $serverStats['idle_worker_num'] ?? null,
                'taskingNum'            => $serverStats['tasking_num'] ?? null,
                'requestCount'          => $serverStats['request_count'] ?? null,
                'workerRequestCount'    => $serverStats['worker_request_count'] ?? null,
                'workerDispatchCount'   => $serverStats['worker_dispatch_count'] ?? null,
                'taskIdleWorkerNum'     => $serverStats['task_idle_worker_num'] ?? null,
                'coroutineNum'          => $serverStats['coroutine_num'] ?? null,
            ],
            'memory' => [
                'usage'                 => memory_get_usage(),
                'allotUsage'            => memory_get_usage(true),
                'peakUsage'             => memory_get_peak_usage(),
                'peakAllotUsage'        => memory_get_peak_usage(true),
            ],
            'coroutine' => [
                'eventNum'              => $coroutineStats['event_num'] ?? null,
                'signalListenerNum'     => $coroutineStats['signal_listener_num'] ?? null,
                'aioTaskNum'            => $coroutineStats['aio_task_num'] ?? null,
                'coroutineNum'          => $coroutineStats['coroutine_num'] ?? null,
                'coroutinePeakNum'      => $coroutineStats['coroutine_peak_num'] ?? null,
            ],
        ];
    }

    protected function addProcess(ProcessInterface $process): void
    {
        $swoole = $this->swoole;
        $appName = $this->getName();
        $enableCoroutine = $this->enableCoroutine;
        $coroutineFlags = $this->coroutineFlags;
        $processHandler = function (Process $worker) use ($swoole, $appName, $process, $enableCoroutine, $coroutineFlags) {
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

            if ($process->enableCoroutine() && $enableCoroutine) {
                Runtime::enableCoroutine(true, $coroutineFlags);
                go($runProcess);
            } else {
                Runtime::enableCoroutine(false);
                $runProcess();
            }
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

        $this->enableCoroutine = array_get($config, 'enable_coroutine', true);
        $this->enableTaskCoroutine = $this->enableCoroutine && (version_compare(swoole_version(), '4.3.0') >= 0);
        $this->coroutineFlags = array_pull($config, 'coroutine_flags', SWOOLE_HOOK_ALL);

        $config['enable_coroutine'] = $this->enableCoroutine;
        $config['task_enable_coroutine'] = $this->enableTaskCoroutine;

        $schedule = array_pull($config, 'schedule');
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

        if ($schedule && is_array($schedule)) {
            $this->addProcess(new ScheduleProcess($schedule));
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
