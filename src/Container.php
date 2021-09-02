<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-31 16:20:05 +0800
 */

namespace Teddy;

use Closure;
use JsonSerializable;
use LogicException;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;

class Container implements ContainerInterface, JsonSerializable
{
    protected static $instance;

    protected $bindings = [];

    protected $instances = [];

    protected $aliases = [];

    public function __construct()
    {
        static::$instance = $this;
    }

    public function jsonSerialize()
    {
        return [
            'bindings'  => array_keys($this->bindings),
            'instances' => array_keys($this->instances),
            'alias'     => $this->aliases,
        ];
    }

    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function bind(string $id, $concrete): ContainerInterface
    {
        $this->bindings[$id] = $concrete;

        return $this;
    }

    public function instance(string $id, $object): ContainerInterface
    {
        $this->instances[$id] = $object;

        return $this;
    }

    public function alias(string $id, string $alias): ContainerInterface
    {
        if ($alias === $id) {
            throw new LogicException("[{$id}] is aliased to itself.");
        }

        $this->aliases[$alias] = $id;

        return $this;
    }

    public function make(string $id, ?array $parameters = null)
    {
        return $this->resolve($id, $parameters);
    }

    public function get(string $id)
    {
        if ($this->has($id)) {
            return $this->resolve($id);
        }

        return null;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id])
               || isset($this->instances[$id])
               || $this->isAlias($id);
    }

    protected function isAlias($alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    protected function getAlias($alias)
    {
        if (!isset($this->aliases[$alias])) {
            return $alias;
        }

        if ($this->aliases[$alias] === $alias) {
            throw new LogicException("[{$alias}] is aliased to itself.");
        }

        return $this->aliases[$alias];
    }

    protected function resolve($id, ?array $parameters = null)
    {
        $id = $this->getAlias($id);

        if (isset($this->instances[$id]) && null === $parameters) {
            return $this->instances[$id];
        }

        $concrete = $this->getConcrete($id);
        $object   = $this->build($concrete, $parameters ?: []);
        if (null !== $object && null === $parameters) {
            $this->instances[$id] = $object;
        }

        return $object;
    }

    protected function getConcrete($id)
    {
        if (isset($this->bindings[$id])) {
            return $this->bindings[$id];
        }

        return $id;
    }

    protected function build($concrete, array $parameters = [])
    {
        $object = null;
        if ($concrete instanceof Closure) {
            $object = $concrete(...$parameters);
        } elseif (class_exists($concrete)) {
            $object = new $concrete(...$parameters);
        } elseif (is_callable($concrete)) {
            $object = $concrete(...$parameters);
        }

        if ($object instanceof ContainerAwareInterface) {
            $object->setContainer($this);
        }

        return $object;
    }
}
