<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-21 10:16:42 +0800
 */

namespace Teddy;

use BadMethodCallException;
use Composer\Autoload\ClassLoader;
use Dotenv\Dotenv;
use Exception;
use Illuminate\Config\Repository as ConfigRepository;
use League\Event\Emitter as EventEmitter;
use League\Event\ListenerInterface;
use Phar;
use Slim\App as SlimApp;
use Teddy\Database\Manager as DatabaseManager;
use Teddy\Factory\ResponseFactory;
use Teddy\Flysystem\Manager as FlysystemManager;
use Teddy\Http\Request;
use Teddy\Http\Response;
use Teddy\Jwt\Manager as JwtManager;
use Teddy\Lock\Factory as LockFactory;
use Teddy\Logger\Manager as LoggerManager;
use Teddy\Middleware\BodyParsingMiddleware;
use Teddy\Middleware\StaticFileMiddleware;
use Teddy\Model\Manager as ModelManager;
use Teddy\Redis\Manager as RedisManager;
use Teddy\Routing\RouteCollector;

abstract class BaseApp extends Container
{
    protected static $loader;

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

    public static function setLoader(ClassLoader $loader): void
    {
        self::$loader = $loader;
    }

    public static function getLoader(): ?ClassLoader
    {
        if (!isset(self::$loader)) {
            $loaderFile = vendor_path('autoload.php');
            if ($loaderFile && is_file($loaderFile)) {
                self::$loader = require $loaderFile;
            }
        }

        return self::$loader;
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
        $this->bind('logger', LoggerManager::class);
        $this->bind('events', EventEmitter::class);
        $this->bind('request', Request::class);
        $this->bind('response', Response::class);
        $this->bind('lock', LockFactory::class);

        if ($this->config->has('database')) {
            $this->bind('db', DatabaseManager::class);
            $this->bind('modelManager', ModelManager::class);
        }

        if ($this->config->has('redis')) {
            $this->bind('redis', RedisManager::class);
        }

        if ($this->config->has('jwt')) {
            $this->bind('jwt', JwtManager::class);
        }

        if ($this->config->has('flysystem')) {
            $this->bind('fs', FlysystemManager::class);
        }
    }

    protected function loadConfigure(): void
    {
        $config = new ConfigRepository;
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

        $this->config = $config;
        $this->instance('config', $config);
    }

    protected function loadEnvironments(string $file = '.env'): void
    {
        try {
            Dotenv::create([$this->getRuntimePath()], $file)->load();
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
                        if (ends_with($file, '.php') && is_file($filepath)) {
                            require $filepath;
                        }
                    }
                });
            }
        }
    }
}
