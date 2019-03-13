<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 15:21:31 +0800
 */
namespace Teddy\Swoole;

use Teddy\Guzzle\DefaultHandler;
use Teddy\Swoole\Traits\HasProcessTitle;
use Teddy\Task;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Swoole\Runtime;
use Swoole\Server\Task as SwooleTask;
use Swoole\Websocket\Server as WebsocketServer;

defined('IN_SWOOLE') || define('IN_SWOOLE', true);

class Server
{
    use HasProcessTitle;

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

    public function __construct(string $basePath, ?callable $callback = null)
    {
        $this->basePath = str_finish($basePath, '/');
        $this->callback = $callback;

        $config = (array) $this->loadPhp('config/swoole.php');
        $config += $this->getDefaultConfig();
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

        if ($this->enableWebsocket) {
            $this->bindWebSocketEvent();
        }
    }

    public function run()
    {
        $this->setProcessTitle(sprintf('%s master', $this->name));
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
                Runtime::enableCoroutine(true);
                $this->setGuzzleHandler();
            }
        } else {
            $process = 'worker';
            if ($this->enableCoroutine) {
                Runtime::enableCoroutine(true);
                $this->setGuzzleHandler();
            }
        }

        $app = $this->loadPhp('bootstrap/app.php');
        if (is_callable($this->callback)) {
            \call_user_func($this->callback, $app);
        }

        $app->getContainer()['server'] = $this;
        $app->getContainer()['swoole'] = $this->swoole;
        $this->app = $app;

        $this->setProcessTitle(sprintf('%s %s', $this->name, $process));
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

    protected function bindWebSocketEvent()
    {
        if ($this->enableWebsocket) {
            $eventHandler = function ($method, array $params) {
                try {
                    \call_user_func_array([$this->websocketHandler, $method], $params);
                } catch (\Exception $e) {
                    $logger = app('logger');
                    if ($logger) {
                        $logger->error(sprintf(
                            'Uncaught exception "%s": [%d]%s called in %s:%d%s%s',
                            get_class($e),
                            $e->getCode(),
                            $e->getMessage(),
                            $e->getFile(),
                            $e->getLine(),
                            PHP_EOL,
                            $e->getTraceAsString()
                        ));
                    }
                }
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

    protected function getDefaultConfig(): array
    {
        $cpuNum = \swoole_cpu_num();

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

    protected function setGuzzleHandler()
    {
        DefaultHandler::setDefaultHandler(GuzzleHandler::class);
        if (!function_exists('\\GuzzleHttp\\set_default_handler')) {
            if (!function_exists('\\GuzzleHttp\\choose_handler')) {
                include_once __DIR__ . '/../Guzzle/functions.php';
            } elseif (extension_loaded('runkit')) {
                runkit_function_redefine('\\GuzzleHttp\\choose_handler', function () {
                    return DefaultHandler::getDefaultHandler();
                });
            }
        }
    }
}
