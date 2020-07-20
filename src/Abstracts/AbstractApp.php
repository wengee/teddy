<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-07-20 12:00:40 +0800
 */

namespace Teddy\Abstracts;

use BadMethodCallException;
use Dotenv\Dotenv;
use Exception;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Support\Str;
use League\Event\ListenerInterface;
use Phar;
use Slim\App as SlimApp;
use Teddy\CallableResolver;
use Teddy\Container;
use Teddy\Factory\ResponseFactory;
use Teddy\Middleware\BodyParsingMiddleware;
use Teddy\Middleware\StaticFileMiddleware;
use Teddy\Routing\RouteCollector;
use Teddy\Utils\Runtime;

abstract class AbstractApp extends Container
{
    protected $basePath = '';

    protected $slimInstance;

    protected $config;

    public function __construct(string $basePath, string $envFile = '.env')
    {
        static::setInstance($this);
        $responseFactory = new ResponseFactory;
        $callableResolver = new CallableResolver($this);
        $routeCollector = new RouteCollector($responseFactory, $callableResolver, $this);
        $this->slimInstance = new SlimApp(
            $responseFactory,
            $this,
            $callableResolver,
            $routeCollector
        );

        $this->setBasePath($basePath);
        $this->loadEnvironments($envFile);
        $this->loadConfigure();
        $this->loadRoutes();
        $this->bootstrap();
    }

    public static function create(string $basePath = '', string $envFile = '.env'): self
    {
        return new static($basePath, $envFile);
    }

    public function __call(string $method, array $args = [])
    {
        if (method_exists($this->slimInstance, $method)) {
            return $this->slimInstance->{$method}(...$args);
        }

        throw new BadMethodCallException("Call to undefined method: $method");
    }

    public function addBodyParsingMiddleware(array $bodyParsers = []): BodyParsingMiddleware
    {
        $bodyParsingMiddleware = new BodyParsingMiddleware($bodyParsers);
        $this->slimInstance->add($bodyParsingMiddleware);
        return $bodyParsingMiddleware;
    }

    public function addStaticFileMiddleware(string $basePath, string $urlPrefix = ''): StaticFileMiddleware
    {
        $middleware = new StaticFileMiddleware($basePath, $urlPrefix);
        $this->slimInstance->add($middleware);
        return $middleware;
    }

    public function getName(): string
    {
        return $this->config->get('app.name') ?: 'Teddy App';
    }

    public function setBasePath(string $basePath): self
    {
        $this->basePath = Str::finish($basePath, '/');
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

    public function addEventListeners(array $list): void
    {
        $emitter = $this->get('events');
        foreach ($list as $event => $listeners) {
            if (!is_array($listeners)) {
                $listeners = [$listeners];
            }

            foreach ($listeners as $listener) {
                if (is_string($listener) &&
                    is_subclass_of($listener, ListenerInterface::class)) {
                    $listener = new $listener;
                }

                $emitter->addListener($event, $listener);
            }
        }
    }

    public function emitEvent($event, ...$args)
    {
        $emitter = $this->get('events');
        if ($emitter) {
            return $emitter->emit($event, ...$args);
        }

        return false;
    }

    public function bootstrap(): void
    {
        $this->instance('app', $this);
        $this->instance('slim', $this->slimInstance);

        $this->alias('snowflake', \Teddy\Interfaces\SnowflakeInterface::class);
        $this->alias('request', \Psr\Http\Message\ServerRequestInterface::class);
        $this->alias('response', \Psr\Http\Message\ResponseInterface::class);
        $this->alias('logger', \Psr\Log\LoggerInterface::class);
        $this->alias('events', \League\Event\ListenerInterface::class);

        $this->bind('logger', \Teddy\Logger\Manager::class);
        $this->bind('events', \League\Event\Emitter::class);
        $this->bind('lock', \Teddy\Lock\Factory::class);
        $this->bind('request', \Teddy\Http\Request::class);
        $this->bind('response', \Teddy\Http\Response::class);
        $this->bind('auth', \Teddy\Auth\Manager::class);
        $this->bind('console', \Teddy\Console\Application::class);

        if ($this->config->has('database')) {
            $this->bind('db', \Teddy\Database\Manager::class);
            $this->bind('modelManager', \Teddy\Model\Manager::class);
        }

        if ($this->config->has('redis')) {
            $this->bind('redis', \Teddy\Redis\Manager::class);
        }

        if ($this->config->has('jwt')) {
            $this->bind('jwt', \Teddy\Jwt\Manager::class);
        }

        if ($this->config->has('flysystem')) {
            $this->bind('fs', \Teddy\Flysystem\Manager::class);
        }

        if ($this->config->has('snowflake')) {
            $this->bind('snowflake', \Teddy\Snowflake\Manager::class);
        }
    }

    public function runConsole(?string $commandName = null): void
    {
        $console = make('console', [$this]);
        if ($commandName) {
            $console->setDefaultCommand($commandName);
        }

        exit($console->run());
    }

    protected function loadConfigure(): void
    {
        $config = new ConfigRepository;
        $dir = $this->basePath . 'config/';
        if (is_dir($dir)) {
            $handle = opendir($dir);
            while (false !== ($file = readdir($handle))) {
                $filepath = $dir . $file;
                if (Str::endsWith($file, '.php') && is_file($filepath)) {
                    $name = substr($file, 0, -4);
                    if ($name !== 'swoole' || Runtime::get() === 'swoole') {
                        $config->set($name, require $filepath);
                    }
                }
            }
        }

        $this->config = $config;
        $this->instance('config', $config);
    }

    protected function loadEnvironments(string $file = '.env'): void
    {
        try {
            Dotenv::createMutable([$this->getRuntimePath()], $file)->load();
        } catch (Exception $e) {
        }
    }

    protected function loadRoutes(): void
    {
        $routesFile = $this->basePath . 'bootstrap/routes.php';
        if (is_file($routesFile)) {
            $this->slimInstance->getRouteCollector()->group([
                'pattern' => $this->config->get('app.urlPrefix', ''),
                'namespace' => '\\App\\Controllers',
            ], function ($router) use ($routesFile): void {
                require $routesFile;
            });
        } else {
            $dir = $this->basePath . 'routes/';
            if (is_dir($dir)) {
                $this->slimInstance->getRouteCollector()->group([
                    'pattern' => $this->config->get('app.urlPrefix', ''),
                    'namespace' => '\\App\\Controllers',
                ], function ($router) use ($dir): void {
                    $handle = opendir($dir);
                    while (false !== ($file = readdir($handle))) {
                        $filepath = $dir . $file;
                        if (Str::endsWith($file, '.php') && is_file($filepath)) {
                            require $filepath;
                        }
                    }
                });
            }
        }
    }
}
