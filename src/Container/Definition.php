<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 15:45:02 +0800
 */

namespace Teddy\Container;

use Exception;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\DefinitionInterface;
use Teddy\Interfaces\LiteralArgumentInterface;
use Teddy\Traits\ContainerAwareTrait;
use Teddy\Traits\ResolveArgumentsTrait;

class Definition implements ContainerAwareInterface, DefinitionInterface
{
    use ContainerAwareTrait;
    use ResolveArgumentsTrait;

    protected string $id;

    protected bool $shared = false;

    protected array $arguments = [];

    protected $concrete;

    protected $resolved;

    public function __construct(string $id, $concrete = null)
    {
        $this->id       = $id;
        $this->concrete = $concrete ?: $id;
    }

    public function setShared(bool $shared = true): self
    {
        $this->shared = $shared;

        return $this;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function setAlias(string $alias): self
    {
        $this->getContainer()->addAlias($alias, $this->id);

        return $this;
    }

    public function addArgument($arg): self
    {
        $this->arguments[] = $arg;

        return $this;
    }

    public function addCollectionArgument(array $args): self
    {
        $this->arguments[] = new CollectionArgument($args);

        return $this;
    }

    public function addLiteralArgument($value, string $type = null): self
    {
        $this->arguments[] = new LiteralArgument($value, $type);

        return $this;
    }

    public function addArguments(array $args): self
    {
        foreach ($args as $arg) {
            $this->addArgument($arg);
        }

        return $this;
    }

    public function resolve()
    {
        if ((null !== $this->resolved) && $this->isShared()) {
            return $this->resolved;
        }

        return $this->resolveNew();
    }

    public function resolveNew(?array $arguments = null)
    {
        $concrete = $this->concrete;

        if (is_callable($concrete)) {
            $concrete = $this->resolveCallable($concrete, $arguments);
        }

        if ($concrete instanceof LiteralArgumentInterface) {
            $this->resolved = $concrete->getValue();

            return $this->resolved;
        }

        if (is_string($concrete) && class_exists($concrete)) {
            $concrete = $this->resolveClass($concrete, $arguments);
        }

        try {
            $container = $this->getContainer();
        } catch (Exception $e) {
            $container = null;
        }

        // if we still have a string, try to pull it from the container
        // this allows for `alias -> alias -> ... -> concrete
        if (is_string($concrete) && ($container instanceof ContainerInterface) && $container->has($concrete)) {
            $concrete = $container->get($concrete);
        }

        if ($concrete instanceof ContainerAwareInterface) {
            $concrete->setContainer($this->getContainer());
        }

        if ($this->isShared() && (null === $arguments)) {
            $this->resolved = $concrete;
        }

        return $concrete;
    }

    protected function resolveCallable(callable $concrete, ?array $arguments = null)
    {
        if (null === $arguments) {
            $arguments = $this->resolveArguments($this->arguments);
        }

        return call_user_func_array($concrete, $arguments);
    }

    protected function resolveClass(string $concrete, ?array $arguments = null): object
    {
        if (null === $arguments) {
            $arguments = $this->resolveArguments($this->arguments);
        }

        return new $concrete(...$arguments);
    }
}
