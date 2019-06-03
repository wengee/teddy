<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-03 09:33:04 +0800
 */
namespace Teddy\Swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Swoole\Runtime;
use Swoole\Server\Task as SwooleTask;
use Swoole\Websocket\Server as WebsocketServer;
use Teddy\Swoole\Traits\HasInotifyProcess;
use Teddy\Swoole\Traits\HasProcessTitle;
use Teddy\Swoole\Traits\HasTimerProcess;
use Teddy\Task;
use Teddy\Utils;

defined('IN_SWOOLE') || define('IN_SWOOLE', true);

class Server
{
    use HasProcessTitle, HasTimerProcess, HasInotifyProcess;

    /**
     * @var boolean
     */
    protected $inited = false;

    /**
     * @var Swoole\Http\Server
     */
    protected $swoole;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var Callable
     */
    protected $callback;

    /**
     * application
     */
    protected $app;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $timerCfg;

    /**
     * @var bool
     */
    protected $enableCoroutine = true;

    /**
     * @var bool
     */
    protected $enableTaskCoroutine = false;

    /**
     * @var bool
     */
    protected $enableWebsocket = false;

    /**
     * @var string
     */
    protected $websocketHandler;

    protected $timerProcess;

    protected $inotifyProcess;

    public function __construct(string $basePath, ?callable $callback = null)
    {
        $this->basePath = str_finish($basePath, '/');
        $this->callback = $callback;
        $this->init();
    }

    public function init()
    {
        if ($this->inited) {
            return;
        }

        $config = $this->loadConfig();
        $this->config = $config;
        $this->name = array_pull($config, 'name', 'slim');
        $this->enableCoroutine = array_get($config, 'enable_coroutine', true);
        $this->enableTaskCoroutine = $this->enableCoroutine && (\version_compare(\swoole_version(), '4.2.12') >= 0);
        $config['task_enable_coroutine'] = $this->enableTaskCoroutine;

        $websocket = array_pull($config, 'websocket', []);
        $this->enableWebsocket = array_get($websocket, 'enable', false);
        $this->websocketHandler = array_get($websocket, 'handler');

        $host = array_pull($config, 'host', '127.0.0.1');
        $port = array_pull($config, 'port', 9500);
        if ($this->enableWebsocket) {
            $this->swoole = new WebsocketServer($host, $port);
        } else {
            $this->swoole = new HttpServer($host, $port);
        }

        $this->swoole->set($config);

        $this->swoole->on('start', [$this, 'onStart']);
        $this->swoole->on('workerStart', [$this, 'onWorkerStart']);
        $this->swoole->on('request', [$this, 'onRequest']);
        if ($this->enableTaskCoroutine) {
            $this->swoole->on('task', [$this, 'onCoTask']);
        } else {
            $this->swoole->on('task', [$this, 'onTask']);
            $this->swoole->on('finish', [$this, 'onFinish']);
        }

        if ($this->enableWebsocket && $this->websocketHandler) {
            $this->bindWebSocketEvent();
        }

        $timerCfg = array_pull($config, 'timer');
        if ($timerCfg && is_array($timerCfg) && isset($timerCfg['enable'])) {
            $this->timerCfg = $timerCfg;
            $this->timerProcess = $this->addTimerProcess($this, $timerCfg, $this->enableCoroutine);
        }

        $inotifyCfg = array_pull($config, 'inotify');
        if ($inotifyCfg && is_array($inotifyCfg) && isset($inotifyCfg['enable'])) {
            $this->inotifyProcess = $this->addInotifyProcess($this, $inotifyCfg);
        }

        $this->inited = true;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSwoole()
    {
        return $this->swoole;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function run()
    {
        if (!$this->inited) {
            $this->init();
        }

        $this->setProcessTitle('master process');
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

        $this->initApp();
        $this->setProcessTitle($process);
    }

    public function onRequest(Request $request, Response $response)
    {
        $slimRequest = RequestTransformer::toSlim($request);
        $slimResponse = $this->app->runWithRequest($slimRequest);
        $response = ResponseTransformer::mergeToSwoole($slimResponse, $response);
        return $response->end();
    }

    public function onCoTask(HttpServer $server, SwooleTask $task)
    {
        $data = $task->data;

        if ($data instanceof Task) {
            $data->setContainer($this->app->getContainer())
                 ->safeRun();
            if (method_exists($data, 'finish')) {
                $task->finish($data->finish());
            }
        }
    }

    public function onTask(HttpServer $server, int $taskId, int $srcWorkerId, $data)
    {
        if ($data instanceof Task) {
            $data->setContainer($this->app->getContainer())
                 ->safeRun();

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

    public function initApp(?bool $enableCoroutine = null)
    {
        $this->initCoroutine($enableCoroutine);
        $app = $this->loadPhp('bootstrap/app.php');
        if (is_callable($this->callback)) {
            call_user_func($this->callback, $app);
        }

        $app->getContainer()['server'] = $this;
        $app->getContainer()['swoole'] = $this->swoole;
        $this->app = $app;
    }

    protected function bindWebSocketEvent()
    {
        if ($this->enableWebsocket) {
            $eventHandler = function ($method, array $params) {
                Utils::callWithCatchException([$this->websocketHandler, $method], $params);
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
    }

    protected function loadPhp(string $path)
    {
        $filepath = $this->basePath . $path;
        if (\file_exists($filepath)) {
            return require $filepath;
        }

        return null;
    }

    protected function loadConfig(): array
    {
        $cpuNum = swoole_cpu_num();
        $defaultConfig = [
            'host' => '127.0.0.1',
            'port' => 9500,
            'enable_coroutine' => true,

            'reactor_num' => $cpuNum * 2,
            'worker_num' => $cpuNum * 2,
            'task_worker_num' => $cpuNum * 2,
            'dispatch_mode' => 1,
            'daemonize' => 0,
        ];

        $config = (array) $this->loadPhp('config/swoole.php');
        $config += $defaultConfig;

        $extraConfigFile = env('SWOOLE_CONFIG', getcwd() . '/swoole.json');
        if (is_file($extraConfigFile)) {
            $extraConfig = json_decode(file_get_contents($extraConfigFile), true);
            if ($extraConfig && is_array($extraConfig)) {
                $config = array_merge($config, $extraConfig);
            }
        }

        return $config;
    }

    protected function initCoroutine(?bool $enableCoroutine = null)
    {
        if ($enableCoroutine === null) {
            $enableCoroutine = $this->enableCoroutine;
        }

        if ($enableCoroutine) {
            Runtime::enableCoroutine(true);
        }
    }
}
