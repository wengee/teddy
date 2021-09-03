<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 15:56:53 +0800
 */

namespace Teddy\Container;

use JsonSerializable;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\DefinitionInterface;
use Teddy\Interfaces\LiteralArgumentInterface;

class Container implements ContainerInterface, JsonSerializable
{
    protected static $instance;

    /** @var array */
    protected $concretes = [];

    public function __construct()
    {
        static::$instance = $this;
    }

    public function jsonSerialize()
    {
        return [
            'concretes' => array_map(function ($item) {
                if ($item instanceof DefinitionInterface) {
                    return '[definition]'.($item->isShared() ? '.[shared]' : '');
                }

                if (is_object($item)) {
                    return get_class($item);
                }

                if (is_callable($item)) {
                    return 'callable';
                }

                return gettype($item);
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
        $concrete   = $concrete ?: $id;
        $definition = new Definition($concrete);
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

    public function get(string $id)
    {
        return $this->resolve($id, null, false);
    }

    public function getNew(string $id, ?array $arguments = null)
    {
        return $this->resolve($id, $arguments, true);
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->concretes);
    }

    protected function resolve(string $id, ?array $arguments = null, bool $new = false)
    {
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
