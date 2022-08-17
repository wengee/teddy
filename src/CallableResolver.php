<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-17 15:57:42 +0800
 */

namespace Teddy;

use Closure;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\Interfaces\CallableResolverInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\WithContainerInterface;

/**
 * This class resolves a string of the format 'class:method' into a closure
 * that can be dispatched.
 */
final class CallableResolver implements WithContainerInterface, CallableResolverInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve toResolve into a callable that the router can dispatch.
     *
     * If toResolve is of the format 'class:method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * @param mixed $toResolve
     *
     * @throws RuntimeException if the callable does not exist
     * @throws RuntimeException if the callable is not resolvable
     */
    public function resolve($toResolve): callable
    {
        $resolved = $toResolve;

        if (!is_callable($toResolve) && is_string($toResolve)) {
            $class    = $toResolve;
            $instance = null;
            $method   = null;

            // check for slim callable as "class:method"
            $callablePattern = '!^([^\:]+)\:{1,2}([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class  = $matches[1];
                $method = $matches[2];
            }

            if (class_exists($class) && !$this->container->has($class)) {
                $this->container->addShared($class);
            }

            $instance = $this->container->get($class);

            // For a class that implements RequestHandlerInterface, we will call handle()
            // if no method has been specified explicitly
            if ($instance instanceof RequestHandlerInterface && null === $method) {
                $method = 'handle';
            }

            $resolved = [$instance, $method ?? '__invoke'];
        }

        if ($resolved instanceof RequestHandlerInterface) {
            $resolved = [$resolved, 'handle'];
        }

        if (!is_callable($resolved)) {
            throw new RuntimeException(sprintf(
                '%s is not resolvable',
                is_array($toResolve) || is_object($toResolve) ? json_encode($toResolve) : $toResolve
            ));
        }

        if ($this->container instanceof ContainerInterface && $resolved instanceof Closure) {
            $resolved = $resolved->bindTo($this->container);
        }

        return $resolved;
    }
}
