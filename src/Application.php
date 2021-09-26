<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-08 18:16:27 +0800
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
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\WithContainerInterface;
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
class Application implements WithContainerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var SlimApp */
    protected $slimApp;

    public function __construct(ContainerInterface $container)
    {
        $container->addValue('app', $this);
        $this->container = $container;
        $this->slimApp   = $container->get('slim');

        $this->initRoutes();
    }

    public function __call(string $method, array $args = [])
    {
        if (method_exists($this->slimApp, $method)) {
            return $this->slimApp->{$method}(...$args);
        }

        throw new BadMethodCallException("Call to undefined method: {$method}");
    }

    public static function create(ContainerInterface $container): self
    {
        return new static($container);
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

    protected function initRoutes(): void
    {
        /** @var RouteCollector $routeCollector */
        $routeCollector = $this->slimApp->getRouteCollector();

        $dir = base_path('routes');
        if (is_dir($dir)) {
            $routeCollector->group([
                'pattern'   => config('app.urlPrefix', ''),
                'namespace' => 'App\\Controllers',
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