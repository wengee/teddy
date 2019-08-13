<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 16:34:35 +0800
 */

namespace Teddy;

use ArrayAccess;
use Closure;
use LogicException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface, ArrayAccess
{
    protected static $instance;

    protected $bindings = [];

    protected $instances = [];

    protected $aliases = [];

    public function bind($abstract, $concrete): Container
    {
        $this->bindings[$abstract] = $concrete;
        return $this;
    }

    public function instance($abstract, $object): Container
    {
        $this->instances[$abstract] = $object;
        return $this;
    }

    public function bound($abstract): bool
    {
        return isset($this->bindings[$abstract]) ||
               isset($this->instances[$abstract]) ||
               $this->isAlias($abstract);
    }

    public function has($id): bool
    {
        return $this->bound($id);
    }

    public function resolved($abstract): bool
    {
        if ($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return isset($this->instances[$abstract]);
    }

    public function isAlias($name): bool
    {
        return isset($this->aliases[$name]);
    }

    public function alias($abstract, $alias): Container
    {
        if ($alias === $abstract) {
            throw new LogicException("[{$abstract}] is aliased to itself.");
        }

        $this->aliases[$alias] = $abstract;
        return $this;
    }

    public function make($abstract, ?array $parameters = null)
    {
        return $this->resolve($abstract, $parameters);
    }

    public function get($id)
    {
        if ($this->has($id)) {
            return $this->resolve($id);
        }

        return null;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function getAlias($abstract)
    {
        if (!isset($this->aliases[$abstract])) {
            return $abstract;
        }

        if ($this->aliases[$abstract] === $abstract) {
            throw new LogicException("[{$abstract}] is aliased to itself.");
        }

        return $this->getAlias($this->aliases[$abstract]);
    }

    public static function getInstance(): ContainerInterface
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public static function setInstance($container)
    {
        static::$instance = $container;
    }

    public function offsetExists($key)
    {
        return $this->bound($key);
    }

    public function offsetGet($key)
    {
        return $this->make($key);
    }

    public function offsetSet($key, $value)
    {
        $this->bind($key, $value instanceof Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->instances[$key]);
    }

    public function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return $concrete(...$parameters);
        } elseif (class_exists($concrete)) {
            return new $concrete(...$parameters);
        } elseif (is_callable($concrete)) {
            return $concrete(...$parameters);
        }

        return null;
    }

    protected function resolve($abstract, ?array $parameters = null)
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract]) && $parameters === null) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConcrete($abstract);
        $object = $this->build($concrete, $parameters ?: []);
        if ($object !== null && $parameters === null) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    protected function getConcrete($abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract];
        }

        return $abstract;
    }
}
