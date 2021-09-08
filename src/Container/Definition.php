<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-08 17:40:44 +0800
 */

namespace Teddy\Container;

use Exception;
use ReflectionClass;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\DefinitionInterface;
use Teddy\Interfaces\LiteralArgumentInterface;
use Teddy\Interfaces\WithContainerInterface;
use Teddy\Traits\ContainerAwareTrait;

class Definition implements ContainerAwareInterface, DefinitionInterface
{
    use ContainerAwareTrait;

    protected $concrete;

    protected $shared = false;

    protected $arguments = [];

    protected $resolved;

    public function __construct($concrete)
    {
        $this->concrete = $concrete;
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

    public function addArgument($arg): self
    {
        $this->arguments[] = $arg;

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

    protected function resolveArguments(array $arguments): array
    {
        try {
            $container = $this->getContainer();
        } catch (Exception $e) {
            $container = null;
        }

        $newArgs = [];
        foreach ($arguments as $arg) {
            if ('container' === $arg) {
                $newArgs[] = $container;

                continue;
            }

            if ($arg instanceof LiteralArgumentInterface) {
                $newArgs[] = $arg->getValue();

                continue;
            }

            if (is_string($arg) && ($container instanceof ContainerInterface) && $container->has($arg)) {
                $newArgs[] = $container->get($arg);
            } else {
                $newArgs[] = $arg;
            }
        }

        return $newArgs;
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
        $reflection = new ReflectionClass($concrete);
        if ($reflection->implementsInterface(WithContainerInterface::class)) {
            return $reflection->newInstanceArgs([$this->getContainer()]);
        }

        if (null === $arguments) {
            $arguments = $this->resolveArguments($this->arguments);
        }

        return $reflection->newInstanceArgs($arguments);
    }
}
