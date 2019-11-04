<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-04 15:00:04 +0800
 */

namespace Teddy\Routing;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteCollectorProxy as SlimRouteCollectorProxy;

class RouteCollectorProxy extends SlimRouteCollectorProxy
{
    protected $namespace = '';

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        CallableResolverInterface $callableResolver,
        ?ContainerInterface $container = null,
        ?RouteCollectorInterface $routeCollector = null,
        string $basePath = ''
    ) {
        $this->responseFactory = $responseFactory;
        $this->callableResolver = $callableResolver;
        $this->container = $container;
        $this->routeCollector = $routeCollector ?? new RouteCollector($responseFactory, $callableResolver, $container);
        $this->basePath = $basePath;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function map(array $methods, string $pattern, $callable): RouteInterface
    {
        if ($this->namespace && is_string($callable) && !is_callable($callable)) {
            $callable = rtrim($this->namespace, '\\') . '\\' . ltrim($callable, '\\');
        }

        return parent::map($methods, $pattern, $callable);
    }

    public function group($pattern, $callable = null): RouteGroupInterface
    {
        $namespace = '';
        if (is_callable($pattern)) {
            $callable = $pattern;
            $pattern = '';
        } elseif (is_array($pattern)) {
            $namespace = $pattern['namespace'] ?? '';
            $pattern = $pattern['pattern'] ?? '';
        }

        $pattern = $this->basePath . $pattern;
        if ($this->namespace && (!$namespace || $namespace{0} !== '\\')) {
            $namespace = rtrim($this->namespace, '\\') . '\\' . $namespace;
        }

        return $this->routeCollector->group([
            'pattern' => $pattern,
            'namespace' => $namespace,
        ], $callable);
    }
}
