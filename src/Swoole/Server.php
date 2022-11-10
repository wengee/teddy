<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 22:55:19 +0800
 */

namespace Teddy\Swoole;

use Exception;
use Illuminate\Support\Arr;
use Swoole\Coroutine;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Swoole\Process;
use Swoole\Runtime;
use Swoole\Server\Task as SwooleTask;
use Swoole\Table;
use Swoole\Websocket\Server as WebsocketServer;
use Teddy\Abstracts\AbstractCommand;
use Teddy\Application;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\QueueInterface;
use Teddy\Interfaces\ServerInterface;
use Teddy\Swoole\Processes\ConsumerProcess;
use Teddy\Swoole\Processes\CrontabProcess;
use Teddy\Traits\TaskAwareTrait;

class Server implements ServerInterface
{
    use TaskAwareTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var null|AbstractCommand
     */
    protected $command;

    /**
     * @var HttpServer|WebsocketServer
     */
    protected $swoole;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var int
     */
    protected $coroutineFlags = SWOOLE_HOOK_ALL;

    public function __construct()
    {
        if (version_compare(SWOOLE_VERSION, '4.6.0') < 0) {
            throw new Exception('Teddy require swoole 4.6.0 or newer.');
        }

        $this->app       = app();
        $this->container = $this->app->getContainer();
        $this->name      = config('app.name') ?: 'Teddy App';

        $this->initialize();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSwoole()
    {
        return $this->swoole;
    }

    public function setCommand(AbstractCommand $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function start(): void
    {
        Util::setProcessTitle('master process', $this->name);
        Coroutine::set(['hook_flags' => $this->coroutineFlags]);
        $this->swoole->start();
    }

    public function onStart(HttpServer $server): void
    {
        $host = config('swoole.host');
        $port = config('swoole.port');
        $this->log('info', 'Listening on '.$host.':'.$port);
    }

    public function onWorkerStart(HttpServer $server, int $workerId): void
    {
        if ($server->taskworker) {
            $processName = 'task worker process';
        } else {
            $processName = 'worker process';
        }

        Runtime::enableCoroutine(true, $this->coroutineFlags);
        Util::setProcessTitle($processName, $this->name);
    }

    public function onRequest(Request $request, Response $response): void
    {
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
    }

    public function onTask(HttpServer $server, SwooleTask $taskData): void
    {
        $this->runTask($taskData->data);
    }

    public function stats(): array
    {
        $serverStats    = $this->swoole->stats();
        $coroutineStats = Coroutine::stats();

        return [
            'hostname'       => gethostname(),
            'currentWorkPid' => getmypid(),
            'phpVersion'     => PHP_VERSION,
            'swooleVersion'  => SWOOLE_VERSION,

            'server' => [
                'startTime'           => $serverStats['start_time'] ?? null,
                'connectionNum'       => $serverStats['connection_num'] ?? null,
                'acceptCount'         => $serverStats['accept_count'] ?? null,
                'closeCount'          => $serverStats['close_count'] ?? null,
                'workerNum'           => $serverStats['worker_num'] ?? null,
                'idleWorkerNum'       => $serverStats['idle_worker_num'] ?? null,
                'taskingNum'          => $serverStats['tasking_num'] ?? null,
                'requestCount'        => $serverStats['request_count'] ?? null,
                'workerRequestCount'  => $serverStats['worker_request_count'] ?? null,
                'workerDispatchCount' => $serverStats['worker_dispatch_count'] ?? null,
                'taskIdleWorkerNum'   => $serverStats['task_idle_worker_num'] ?? null,
                'coroutineNum'        => $serverStats['coroutine_num'] ?? null,
            ],

            'memory' => [
                'usage'          => memory_get_usage(),
                'allotUsage'     => memory_get_usage(true),
                'peakUsage'      => memory_get_peak_usage(),
                'peakAllotUsage' => memory_get_peak_usage(true),
            ],

            'coroutine' => [
                'eventNum'          => $coroutineStats['event_num'] ?? null,
                'signalListenerNum' => $coroutineStats['signal_listener_num'] ?? null,
                'aioTaskNum'        => $coroutineStats['aio_task_num'] ?? null,
                'coroutineNum'      => $coroutineStats['coroutine_num'] ?? null,
                'coroutinePeakNum'  => $coroutineStats['coroutine_peak_num'] ?? null,
            ],
        ];
    }

    public function addProcess(ProcessInterface $process): Process
    {
        $swoole          = $this->swoole;
        $appName         = $this->getName();
        $enableCoroutine = $process->getOption('coroutine');
        $coroutineFlags  = $this->coroutineFlags;
        $processHandler  = function (Process $worker) use ($swoole, $appName, $process, $enableCoroutine, $coroutineFlags): void {
            $process->setWorker($worker);

            if ($enableCoroutine) {
                Runtime::enableCoroutine(true, $coroutineFlags);
            } else {
                Runtime::enableCoroutine(false);
            }

            $name = $process->getName() ?: 'custom';
            Util::setProcessTitle($name, $appName);

            Process::signal(SIGUSR1, function ($signo) use ($name, $process, $worker, $swoole): void {
                log_message(null, 'INFO', 'Reloading the process %s [pid=%d].', $name, $worker->pid);

                if (method_exists($process, 'onReload')) {
                    safe_call([$process, 'onReload'], [$swoole, $worker]);
                }
            });

            log_message(null, 'INFO', 'Run the process %s [pid=%d].', $name, $worker->pid);
            if (method_exists($process, 'handle')) {
                safe_call([$process, 'handle'], [$swoole, $worker]);
            }
        };

        $customProcess = new Process($processHandler, false, 0, $enableCoroutine);
        $swoole->addProcess($customProcess);

        return $customProcess;
    }

    protected function addCustomProcesses(array $processes): void
    {
        foreach ($processes as $key => $item) {
            $className = null;
            $args      = [];

            if (is_integer($key)) {
                if (is_string($item)) {
                    $className = $item;
                } elseif (is_array($item)) {
                    $className = $item['class'] ?? null;
                    $args      = $item['parameters'] ?? [];
                }
            } elseif (is_string($key)) {
                $className = $key;
                if (is_array($item)) {
                    $args = $item;
                }
            }

            if (!$className || !class_exists($className)) {
                continue;
            }

            $args    = is_array($args) ? $args : [$args];
            $process = new $className(...$args);
            if (!($process instanceof ProcessInterface)) {
                continue;
            }

            $count = $process->getOption('count', 0);
            if ($count > 0) {
                for ($i = 0; $i < $count; ++$i) {
                    $this->addProcess($process);
                }
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
            if (isset($clientInfo['websocket_status']) && \WEBSOCKET_STATUS_FRAME === $clientInfo['websocket_status']) {
                $eventHandler('onClose', func_get_args());
            }
        });
    }

    protected function createSwooleTables(array $tables): void
    {
        foreach ($tables as $name => $table) {
            $columns = Arr::wrap($table['columns'] ?? []);
            if (!$columns) {
                continue;
            }

            $t = new Table($table['size'] ?? 1024);
            foreach ($columns as $column) {
                if (!is_array($column) || !isset($column['name'])) {
                    continue;
                }

                if (isset($column['size'])) {
                    $t->column($column['name'], $column['type'] ?? Table::TYPE_INT, $column['size'] ?: 1);
                } else {
                    $t->column($column['name'], $column['type'] ?? Table::TYPE_INT);
                }
            }

            $t->create();
            $name .= 'Table';
            $this->swoole->{$name} = $t;
        }
    }

    protected function initialize(): void
    {
        $config = config('swoole');

        $enableWebsocket  = Arr::pull($config, 'websocket.enabled', false);
        $websocketHandler = Arr::pull($config, 'websocket.handler');

        $host = $config['host'] ?: '127.0.0.1';
        $port = $config['port'] ?: 9500;
        if ($enableWebsocket) {
            $this->swoole = new WebsocketServer($host, $port);
        } else {
            $this->swoole = new HttpServer($host, $port);
        }

        $options = $config['options'];

        $this->coroutineFlags = Arr::pull($options, 'coroutine_flags', SWOOLE_HOOK_ALL);

        $options['enable_coroutine']      = true;
        $options['task_enable_coroutine'] = true;
        $this->swoole->set($options);

        $this->container->addValue('server', $this);
        $this->container->addValue(ServerInterface::class, $this);
        $this->container->addValue('swoole', $this->swoole);

        if (!$this->container->has(QueueInterface::class)) {
            $this->container->add(QueueInterface::class, Queue::class);
        }

        $this->swoole->on('start', [$this, 'onStart']);
        $this->swoole->on('workerStart', [$this, 'onWorkerStart']);
        $this->swoole->on('request', [$this, 'onRequest']);
        $this->swoole->on('task', [$this, 'onTask']);

        if ($enableWebsocket) {
            if (is_subclass_of($websocketHandler, WebsocketHandlerInterface::class)) {
                $websocketHandler = is_string($websocketHandler) ? new $websocketHandler() : $websocketHandler;
                $this->bindWebSocketEvent($websocketHandler);
            }
        }

        $crontab = config('crontab');
        if ($config['crontab'] && is_array($crontab) && $crontab) {
            $this->addProcess(new CrontabProcess($crontab));
        }

        if ($config['consumer']) {
            $channels = config('queue.channels');
            $this->addProcess(new ConsumerProcess($this->container, $channels));
        }

        $processes = config('process');
        if (is_array($processes) && $processes) {
            $this->addCustomProcesses($processes);
        }

        if ($config['tables'] && is_array($config['tables'])) {
            $this->createSwooleTables($config['tables']);
        }
    }

    protected function log(string $type, string $message): void
    {
        $message = date('[Y-m-d H:i:s] ').$message;
        if ($this->command) {
            $this->command->{$type}($message);
        } else {
            echo $message."\n";
        }
    }
}
