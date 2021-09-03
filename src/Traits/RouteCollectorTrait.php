<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Traits;

use Teddy\Interfaces\RouteGroupInterface;
use Teddy\Interfaces\RouteInterface;

trait RouteCollectorTrait
{
    public function delete(string $path, $handler): RouteInterface
    {
        return $this->map(['DELETE'], $path, $handler);
    }

    public function get(string $path, $handler): RouteInterface
    {
        return $this->map(['GET'], $path, $handler);
    }

    public function head(string $path, $handler): RouteInterface
    {
        return $this->map(['HEAD'], $path, $handler);
    }

    public function options(string $path, $handler): RouteInterface
    {
        return $this->map(['OPTIONS'], $path, $handler);
    }

    public function patch(string $path, $handler): RouteInterface
    {
        return $this->map(['PATCH'], $path, $handler);
    }

    public function post(string $path, $handler): RouteInterface
    {
        return $this->map(['POST'], $path, $handler);
    }

    public function put(string $path, $handler): RouteInterface
    {
        return $this->map(['PUT'], $path, $handler);
    }

    /** @param array|string $options */
    abstract public function group($options, callable $callable): RouteGroupInterface;

    /** @param string[] $methods */
    abstract public function map($methods, string $path, $handler): RouteInterface;
}
