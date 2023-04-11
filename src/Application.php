<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-11 14:05:55 +0800
 */

namespace Teddy;

use BadMethodCallException;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use Slim\App as SlimApp;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\KernelInterface;
use Teddy\Middleware\AccessLogMiddleware;
use Teddy\Middleware\CrossOriginMiddleware;
use Teddy\Middleware\JsonResponseMiddleware;
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

    protected string $name = '';

    protected SlimApp $slimApp;

    public function __construct(ContainerInterface $container)
    {
        $container->addValue('app', $this);
        $this->container = $container;
        $this->slimApp   = $container->get('slim');
        $this->name      = config('app.name', 'Teddy App');

        $this->setDefaultTimezone();
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

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param callable|MiddlewareInterface|string $middleware
     */
    public function add($middleware): self
    {
        $this->slimApp->add($middleware);

        return $this;
    }

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->slimApp->addMiddleware($middleware);

        return $this;
    }

    public function addCorsMiddleware(): CrossOriginMiddleware
    {
        $middleware = new CrossOriginMiddleware();
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

    public function addAccessLogMiddleware(?string $logger = null): AccessLogMiddleware
    {
        $middleware = new AccessLogMiddleware($logger);
        $this->slimApp->add($middleware);

        return $middleware;
    }

    public function addJsonResponseMiddleware(): JsonResponseMiddleware
    {
        $middleware = new JsonResponseMiddleware();
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
        $this->getContainer()
            ->get(KernelInterface::class)
            ->handle(new ArgvInput(), new ConsoleOutput())
        ;
    }

    protected function initRoutes(): void
    {
        /**
         * @var RouteCollector $routeCollector
         */
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

    protected function setDefaultTimezone(): void
    {
        $timezone = config('app.timezone', 'UTC');

        try {
            @date_default_timezone_set($timezone);
        } catch (Exception $e) {
        }
    }
}
