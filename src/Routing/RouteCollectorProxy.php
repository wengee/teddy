<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Routing;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteCollectorProxy as SlimRouteCollectorProxy;

/**
 * @property RouteCollector $routeCollector
 */
class RouteCollectorProxy extends SlimRouteCollectorProxy
{
    protected $namespace = '';

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        CallableResolverInterface $callableResolver,
        ?ContainerInterface $container = null,
        ?RouteCollectorInterface $routeCollector = null,
        string $groupPattern = ''
    ) {
        $this->responseFactory  = $responseFactory;
        $this->callableResolver = $callableResolver;
        $this->container        = $container;
        $this->routeCollector   = $routeCollector ?? new RouteCollector($responseFactory, $callableResolver, $container);
        $this->groupPattern     = $groupPattern;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function map(array $methods, string $pattern, $callable): RouteInterface
    {
        if ($this->namespace && is_string($callable) && !is_callable($callable)) {
            $callable = rtrim($this->namespace, '\\').'\\'.ltrim($callable, '\\');
        }

        return parent::map($methods, $pattern, $callable);
    }

    /**
     * @param null|array|callable|string $pattern
     * @param null|callable              $callable
     */
    public function group($pattern, $callable = null): RouteGroupInterface
    {
        $namespace = '';
        if (is_callable($pattern)) {
            $callable = $pattern;
            $pattern  = '';
        } elseif (is_array($pattern)) {
            $namespace = $pattern['namespace'] ?? '';
            $pattern   = $pattern['pattern'] ?? '';
        }

        $pattern = $this->groupPattern.$pattern;
        if ($this->namespace && (!$namespace || '\\' !== $namespace[0])) {
            $namespace = rtrim($this->namespace, '\\').'\\'.$namespace;
        }

        return $this->routeCollector->group([
            'pattern'   => $pattern,
            'namespace' => $namespace,
        ], $callable);
    }
}
