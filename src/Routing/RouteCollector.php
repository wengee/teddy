<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-05 15:58:01 +0800
 */

namespace Teddy\Routing;

use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Routing\RouteCollector as SlimRouteCollector;
use Slim\Routing\RouteGroup;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\WithContainerInterface;

class RouteCollector extends SlimRouteCollector implements WithContainerInterface
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct(
            $container->get(ResponseFactoryInterface::class),
            $container->get(CallableResolverInterface::class),
            $container,
            $container->get(InvocationStrategyInterface::class),
            $container->get(RouteParserInterface::class)
        );
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
        } elseif (is_string($pattern) && preg_match('/^([^:]+):([^:]+)$/i', $pattern, $m)) {
            $namespace = $m[1];
            $pattern   = $m[2];
        }

        $routeCollectorProxy = new RouteCollectorProxy(
            $this->responseFactory,
            $this->callableResolver,
            $this->container,
            $this,
            $pattern
        );

        if ($namespace) {
            $routeCollectorProxy->setNamespace($namespace);
        }

        $routeGroup          = new RouteGroup($pattern, $callable, $this->callableResolver, $routeCollectorProxy);
        $this->routeGroups[] = $routeGroup;

        $routeGroup->collectRoutes();
        array_pop($this->routeGroups);

        return $routeGroup;
    }
}
