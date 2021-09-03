<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 12:09:50 +0800
 */

namespace Teddy;

use BadMethodCallException;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App as SlimApp;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;
use Teddy\Config\Config;
use Teddy\Console\Application as ConsoleApplication;
use Teddy\Factory\ContainerFactory;
use Teddy\Factory\ResponseFactory;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Middleware\CorsMiddleware;
use Teddy\Middleware\ProxyFixMiddleware;
use Teddy\Middleware\StaticFileMiddleware;
use Teddy\Routing\RouteCollector;
use Teddy\Traits\ContainerAwareTrait;

/**
 * @method BodyParsingMiddleware addBodyParsingMiddleware(array $bodyParsers = [])
 * @method RoutingMiddleware     addRoutingMiddleware()
 * @method ErrorMiddleware       addErrorMiddleware(bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails, ?LoggerInterface $logger = null)
 */
class Application implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var SlimApp */
    protected $slimApp;

    /** @var Config */
    protected $config;

    public function __construct(string $basePath)
    {
        $container = ContainerFactory::create($basePath);
        $container->addValue('app', $this);
        $this->setContainer($container);

        $this->config = $container->get('config');

        $this->initSlimApp();
        $this->initRoutes($basePath);
    }

    public function __call(string $method, array $args = [])
    {
        if (method_exists($this->slimApp, $method)) {
            return $this->slimApp->{$method}(...$args);
        }

        throw new BadMethodCallException("Call to undefined method: {$method}");
    }

    public static function create(string $basePath = ''): self
    {
        return new static($basePath);
    }

    public function addCorsMiddleware(): CorsMiddleware
    {
        $middleware = new CorsMiddleware();
        $this->slimApp->add($middleware);

        return $middleware;
    }

    public function addProxyFixMiddleware(): ProxyFixMiddleware
    {
        $middleware = new ProxyFixMiddleware();
        $this->slimApp->add($middleware);

        return $middleware;
    }

    public function addStaticFileMiddleware(string $basePath, string $urlPrefix = ''): StaticFileMiddleware
    {
        $middleware = new StaticFileMiddleware($basePath, $urlPrefix);
        $this->slimApp->add($middleware);

        return $middleware;
    }

    public function getSlimApp(): SlimApp
    {
        return $this->slimApp;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->slimApp->handle($request);
    }

    public function run(): void
    {
        $console = new ConsoleApplication($this);
        $console->setContainer($this->getContainer());

        $console->run();
    }

    protected function initSlimApp(): void
    {
        $responseFactory  = new ResponseFactory();
        $callableResolver = new CallableResolver($this->container);
        $routeCollector   = new RouteCollector($responseFactory, $callableResolver, $this->container);
        $this->slimApp    = new SlimApp(
            $responseFactory,
            $this->container,
            $callableResolver,
            $routeCollector
        );

        $this->container->addValue('slim', $this->slimApp);
    }

    protected function initRoutes(string $basePath): void
    {
        /** @var RouteCollector $routeCollector */
        $routeCollector = $this->slimApp->getRouteCollector();

        $dir = path_join($basePath, 'routes');
        if (is_dir($dir)) {
            $routeCollector->group([
                'pattern'   => $this->config->get('app.urlPrefix', ''),
                'namespace' => '\\App\\Controllers',
            ], function ($router) use ($dir): void {
                $handle = opendir($dir);
                while (false !== ($file = readdir($handle))) {
                    $filepath = path_join($dir, $file);
                    if (Str::endsWith($file, '.php') && is_file($filepath)) {
                        require $filepath;
                    }
                }

                closedir($handle);
            });
        }
    }
}
