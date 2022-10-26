<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-26 17:07:17 +0800
 */

namespace Teddy\Container;

use JsonSerializable;
use LogicException;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\DefinitionInterface;
use Teddy\Interfaces\LiteralArgumentInterface;

class Container implements ContainerInterface, JsonSerializable
{
    /**
     *  @var self
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var array
     */
    protected $concretes = [];

    private function __construct()
    {
        $this->concretes['container']                  = $this;
        $this->concretes[ContainerInterface::class]    = $this;
        $this->concretes[PsrContainerInterface::class] = $this;
    }

    private function __clone()
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'alias'     => $this->aliases,
            'concretes' => array_map(function ($item) {
                if ($item instanceof DefinitionInterface) {
                    return '[definition]'.($item->isShared() ? '.[shared]' : '');
                }

                if (is_object($item)) {
                    return '['.get_class($item).']';
                }

                if (is_callable($item)) {
                    return '[callable]';
                }

                if (is_string($item) || method_exists($item, '__toString')) {
                    return (string) $item;
                }

                return '['.gettype($item).']';
            }, $this->concretes),
        ];
    }

    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function add(string $id, $concrete = null, bool $shared = false): DefinitionInterface
    {
        $definition = new Definition($id, $concrete);
        $definition->setContainer($this);
        if ($shared) {
            $definition->setShared(true);
        }

        $this->concretes[$id] = $definition;
        $this->removeAlias($id);

        return $definition;
    }

    public function addShared(string $id, $concrete = null): DefinitionInterface
    {
        return $this->add($id, $concrete, true);
    }

    public function addValue(string $id, $value): void
    {
        $this->concretes[$id] = $value;
        $this->removeAlias($id);
    }

    public function addAlias(string $id, string $alias): void
    {
        if ($id === $alias) {
            throw new LogicException("[{$id}] is aliased to itself.");
        }

        $this->aliases[$id] = $alias;
    }

    public function remove(string $id): void
    {
        if (isset($this->concretes[$id])) {
            unset($this->concretes[$id]);
        }

        $this->removeAlias($id);
    }

    public function removeAlias(string $id): void
    {
        if (isset($this->aliases[$id])) {
            unset($this->aliases[$id]);
        }
    }

    public function get(string $id)
    {
        return $this->resolve($id, null, false);
    }

    public function getNew(string $id, ?array $arguments = null)
    {
        return $this->resolve($id, $arguments, true);
    }

    public function getAlias(string $id)
    {
        return $this->aliases[$id] ?? $id;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->aliases) || array_key_exists($id, $this->concretes);
    }

    protected function resolve(string $id, ?array $arguments = null, bool $new = false)
    {
        $id       = $this->getAlias($id);
        $concrete = $this->concretes[$id] ?? null;
        if ($concrete instanceof DefinitionInterface) {
            return $new ? $concrete->resolveNew($arguments) : $concrete->resolve();
        }

        if ($concrete instanceof LiteralArgumentInterface) {
            return $concrete->getValue();
        }

        return $concrete;
    }
}
