<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-09 14:15:25 +0800
 */

namespace Teddy\Container;

use JsonSerializable;
use LogicException;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\DefinitionInterface;
use Teddy\Interfaces\LiteralArgumentInterface;

class Container implements ContainerInterface, JsonSerializable
{
    protected static $instance;

    /** @var array */
    protected $aliases = [];

    /** @var array */
    protected $concretes = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup(): void
    {
    }

    public function jsonSerialize()
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

    public function add(string $id, $concrete = null): DefinitionInterface
    {
        $definition = new Definition($id, $concrete);
        $definition->setContainer($this);

        $this->concretes[$id] = $definition;

        return $definition;
    }

    public function addShared(string $id, $concrete = null): DefinitionInterface
    {
        $definition = $this->add($id, $concrete);
        $definition->setShared(true);

        return $definition;
    }

    public function addValue(string $id, $value): void
    {
        $this->concretes[$id] = $value;
    }

    public function addAlias(string $id, string $alias): void
    {
        if ($id === $alias) {
            throw new LogicException("[{$id}] is aliased to itself.");
        }

        $this->aliases[$id] = $alias;
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
