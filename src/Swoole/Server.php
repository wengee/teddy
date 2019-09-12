<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-09-12 10:15:20 +0800
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
use Teddy\App;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\WebsocketHandlerInterface;
use Teddy\Schedule\ScheduleProcess;
use Teddy\Task;
use Teddy\Utils;

defined('IN_SWOOLE') || define('IN_SWOOLE', true);

class Server
{
    protected $name = 'Teddy Server';

    protected $swoole;

    protected $config;

    protected $coroutineFlags = SWOOLE_HOOK_ALL;

    public function __construct(App $app, array $config = [])
    {
        if (version_compare(PHP_VERSION, '7.3.0') < 0) {
            throw new Exception('Teddy require PHP 7.3 or newer.');
        }

        if (version_compare(SWOOLE_VERSION, '4.4.0') < 0) {
            throw new Exception('Teddy require swoole 4.4.0 or newer.');
        }

        $this->app = $app;
        $this->name = $app->getName();

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

    public function start(): void
    {
        Utils::setProcessTitle('master process', $this->name);
        $this->swoole->start();
    }

    public function onStart(HttpServer $server): void
    {
        $this->app->emitEvent('server.onStart');
    }

    public function onWorkerStart(HttpServer $server, int $workerId): void
    {
        $workerNum = array_get($this->config, 'options.worker_num', 1);
        if ($workerId >= $workerNum) {
            $this->app->emitEvent('server.onTaskWorkerStart');

            $processName = 'task worker process';
            Runtime::enableCoroutine(true, $this->coroutineFlags);
        } else {
            $this->app->emitEvent('server.onWorkerStart');

            $processName = 'worker process';
            Runtime::enableCoroutine(true, $this->coroutineFlags);
        }

        Utils::setProcessTitle($processName, $this->name);
    }

    public function onRequest(Request $request, Response $response): void
    {
        try {
            $this->app->run($request, $response);
        } catch (Exception $e) {
            log_exception($e);
            $response->detach();

            $response = Response::create($request->fd);
            $response->status(500);
            $response->end('Internal Server Error');
        }
    }

    public function onTask(HttpServer $server, SwooleTask $task): void
    {
        $data = $task->data;

        if ($data instanceof Task) {
            $data->safeRun();
            if ($data->isWaiting()) {
                $task->finish($data->finish());
            }
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

    public function addProcess(ProcessInterface $process): Process
    {
        $swoole = $this->swoole;
        $appName = $this->getName();
        $enableCoroutine = $process->enableCoroutine();
        $coroutineFlags = $this->coroutineFlags;
        $processHandler = function (Process $worker) use ($swoole, $appName, $process, $enableCoroutine, $coroutineFlags): void {
            if ($enableCoroutine) {
                Runtime::enableCoroutine(true, $coroutineFlags);
            } else {
                Runtime::enableCoroutine(false);
            }

            $name = $process->getName() ?: 'custom';
            Utils::setProcessTitle($name, $appName);

            Process::signal(SIGUSR1, function ($signo) use ($name, $process, $worker, $swoole): void {
                log_message('info', 'Reloading the process %s [pid=%d].', [$name, $worker->pid]);
                $process->onReload($swoole, $worker);
            });

            log_message('info', 'Run the process %s [pid=%d].', [$name, $worker->pid]);
            safe_call([$process, 'handle'], [$swoole, $worker]);
        };

        $customProcess = new Process($processHandler, false, 0, $enableCoroutine);
        $swoole->addProcess($customProcess);
        return $customProcess;
    }

    protected function addProcesses(array $processes): void
    {
        foreach ($processes as $key => $item) {
            $className = null;
            $args = [];
            $total = 1;

            if (is_integer($key)) {
                if (is_string($item)) {
                    $className = $item;
                } elseif (is_array($item)) {
                    $className = array_get($item, 'class');
                    $args = array_get($item, 'parameters', []);
                    $total = array_get($item, 'total', 1);
                }
            } elseif (is_string($key)) {
                $className = $key;
                if (is_array($item)) {
                    $args = $item;
                } elseif (is_integer($item)) {
                    $total = $item;
                }
            }

            if (!$className || !class_exists($className) || $total < 1) {
                continue;
            }

            $args = is_array($args) ? $args : [$args];
            for ($i = 0; $i < $total; $i ++) {
                $this->addProcess(new $className(...$args));
            }
        }
    }

    protected function bindWebSocketEvent(WebsocketHandlerInterface $websocketHandler): void
    {
        $eventHandler = function ($method, array $params) use ($websocketHandler): void {
            safe_call([$websocketHandler, $method], $params);
        };

        $this->swoole->on('open', function (...$args) use ($eventHandler): void {
            $eventHandler('onOpen', $args);
        });

        $this->swoole->on('message', function (...$args) use ($eventHandler): void {
            $eventHandler('onMessage', $args);
        });

        $this->swoole->on('close', function (WebsocketServer $server, int $fd, int $reactorId) use ($eventHandler): void {
            $clientInfo = $server->getClientInfo($fd);
            if (isset($clientInfo['websocket_status']) && $clientInfo['websocket_status'] === \WEBSOCKET_STATUS_FRAME) {
                $eventHandler('onClose', func_get_args());
            }
        });
    }

    protected function init(array $config): void
    {
        $config = $this->parseConfig($config);

        $enableWebsocket = array_pull($config, 'websocket.enable', false);
        $websocketHandler = array_pull($config, 'websocket.handler');

        $host = $config['host'] ?: '127.0.0.1';
        $port = $config['port'] ?: 9500;
        if ($enableWebsocket) {
            $this->swoole = new WebsocketServer($host, $port);
        } else {
            $this->swoole = new HttpServer($host, $port);
        }

        $options = $config['options'];
        $this->coroutineFlags = array_pull($options, 'coroutine_flags', SWOOLE_HOOK_ALL);

        $options['enable_coroutine'] = true;
        $options['task_enable_coroutine'] = true;
        $this->swoole->set($options);

        $this->app->instance('server', $this);
        $this->app->instance('swoole', $this->swoole);

        $this->swoole->on('start', [$this, 'onStart']);
        $this->swoole->on('workerStart', [$this, 'onWorkerStart']);
        $this->swoole->on('request', [$this, 'onRequest']);
        $this->swoole->on('task', [$this, 'onTask']);

        if ($enableWebsocket) {
            if (is_subclass_of($websocketHandler, WebsocketHandlerInterface::class)) {
                $websocketHandler = is_string($websocketHandler) ? new $websocketHandler :$websocketHandler;
                $this->bindWebSocketEvent($websocketHandler);
            }
        }

        if ($config['schedule'] && is_array($config['schedule'])) {
            $this->addProcess(new ScheduleProcess($config['schedule']));
        }

        if ($config['processes'] && is_array($config['processes'])) {
            $this->addProcesses($config['processes']);
        }
    }

    protected function parseConfig(array $config = []): array
    {
        $cpuNum = swoole_cpu_num();
        $options = [
            'reactor_num' => $cpuNum * 2,
            'worker_num' => $cpuNum * 2,
            'task_worker_num' => $cpuNum * 2,
            'dispatch_mode' => 1,
            'daemonize' => 0,
        ];

        if (isset($config['options']) && is_array($config['options'])) {
            $options = $config['options'] + $options;
        }

        $config = $config + [
            'host' => '127.0.0.1',
            'port' => 9500,

            'schedule' => null,
            'processes' => null,

            'options' => [
                'reactor_num' => $cpuNum * 2,
                'worker_num' => $cpuNum * 2,
                'task_worker_num' => $cpuNum * 2,
                'dispatch_mode' => 1,
                'daemonize' => 0,
            ],
        ];

        $config['options'] = $options;
        $this->config = $config;
        return $config;
    }
}
