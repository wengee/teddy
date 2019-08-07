<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:35:28 +0800
 */

namespace Teddy;

use Dotenv\Dotenv;
use Exception;
use Illuminate\Config\Repository as ConfigRepository;
use Phar;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\MiddlewareDispatcher;
use Slim\Routing\RouteResolver;
use Slim\Routing\RouteRunner;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Teddy\Factory\ResponseFactory;
use Teddy\Factory\ServerRequestFactory;
use Teddy\Http\Request;
use Teddy\Http\Response;
use Teddy\Providers\Jwt;
use Teddy\Routing\RouteCollectorProxy;
use Teddy\Swoole\Server;

class App extends Container implements RequestHandlerInterface
{
    protected $basePath = '';

    protected $responseFactory;

    protected $callableResolver;

    protected $middlewareDispatcher;

    protected $router;

    public function __construct(string $basePath)
    {
        static::setInstance($this);

        $this->setBasePath($basePath);
        $this->responseFactory = new ResponseFactory;
        $this->callableResolver = new CallableResolver($this);

        $this->router = new RouteCollectorProxy(
            $this->responseFactory,
            $this->callableResolver,
            $this
        );

        $routeResolver = new RouteResolver($this->router->getRouteCollector());
        $routeRunner = new RouteRunner(
            $routeResolver,
            $this->router->getRouteCollector()->getRouteParser()
        );
        $this->middlewareDispatcher = new MiddlewareDispatcher($routeRunner, $this);

        $this->loadEnvironments();
        $this->loadConfigure();
        $this->bootstrapContainer();
        $this->loadRoutes();
    }

    public static function create(string $basePath = '')
    {
        return new static($basePath);
    }

    public function setBasePath(string $basePath): self
    {
        $this->basePath = str_finish($basePath, '/');
        return $this;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getRuntimePath(): string
    {
        $pharPath = Phar::running(false);
        if ($pharPath) {
            return dirname($pharPath);
        }

        return $this->getBasePath();
    }

    public function getRouter(): RouteCollectorProxyInterface
    {
        return $this->router;
    }

    public function add($middleware): self
    {
        $this->middlewareDispatcher->add($middleware);
        return $this;
    }

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewareDispatcher->addMiddleware($middleware);
        return $this;
    }

    public function run(
        SwooleRequest $swooleRequest,
        SwooleResponse $swooleResponse
    ): void {
        $request = ServerRequestFactory::createServerRequestFromSwoole($swooleRequest);
        $response = $this->handle($request);
        $responseEmitter = new ResponseEmitter($swooleResponse);
        $responseEmitter->emit($response);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->middlewareDispatcher->handle($request);
        $method = strtoupper($request->getMethod());
        if ($method === 'HEAD') {
            $emptyBody = $this->responseFactory->createResponse()->getBody();
            return $response->withBody($emptyBody);
        }

        return $response;
    }

    public function listen()
    {
        $config = $this->get('config')->get('swoole', []);
        (new Server($this, $config))->start();
    }

    protected function bootstrapContainer(): void
    {
        $this->instance('app', $this);
        $this->bind('request', Request::class);
        $this->bind('response', Response::class);
        $this->bind('jwt', Jwt::class);
    }

    protected function loadConfigure(): void
    {
        $config = new ConfigRepository;
        $this->instance('config', $config);

        $dir = $this->basePath . 'config/';
        if (is_dir($dir)) {
            $handle = opendir($dir);
            while (false !== ($file = readdir($handle))) {
                $filepath = $dir . $file;
                if (ends_with($file, '.php') && is_file($filepath)) {
                    $name = substr($file, 0, -4);
                    $config->set($name, require $filepath);
                }
            }
        }
    }

    protected function loadEnvironments(string $file = '.env'): void
    {
        $paths = [
            $this->getRuntimePath(),
            getcwd(),
            getenv('HOME') ?: '/'
        ];

        try {
            Dotenv::create($paths, $file)->load();
        } catch (Exception $e) {
        }
    }

    protected function loadRoutes(): void
    {
        $dir = $this->basePath . 'routes/';
        if (is_dir($dir)) {
            $this->router->group([
                'pattern' => '',
                'namespace' => 'App\Controllers',
            ], function ($router) use ($dir) {
                $handle = opendir($dir);
                while (false !== ($file = readdir($handle))) {
                    $filepath = $dir . $file;
                    if (ends_with($file, '.php') && is_file($filepath)) {
                        require $filepath;
                    }
                }
            });
        }
    }
}
