<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-26 15:59:47 +0800
 */

namespace Teddy\Routing;

use Slim\Interfaces\RouteGroupInterface;
use Slim\Routing\RouteCollector as SlimRouteCollector;
use Slim\Routing\RouteGroup;

class RouteCollector extends SlimRouteCollector
{
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
