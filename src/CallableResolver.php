<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 14:47:38 +0800
 */
namespace SlimExtra;

use Interop\Container\ContainerInterface;
use RuntimeException;
use Slim\Interfaces\CallableResolverInterface;

final class CallableResolver implements CallableResolverInterface
{
    const CALLABLE_PATTERN = '!^([^\:]+)[@\:]([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve toResolve into a closure so that the router can dispatch.
     *
     * If toResolve is of the format 'class:method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * @param mixed $toResolve
     *
     * @return callable
     *
     * @throws RuntimeException if the callable does not exist
     * @throws RuntimeException if the callable is not resolvable
     */
    public function resolve($toResolve)
    {
        if (is_callable($toResolve)) {
            return $toResolve;
        }

        if (!is_string($toResolve)) {
            $this->assertCallable($toResolve);
        }

        // check for slim callable as "class:method"
        if (preg_match(self::CALLABLE_PATTERN, $toResolve, $matches)) {
            $resolved = $this->resolveCallable($matches[1], $matches[2]);
            $this->assertCallable($resolved);

            return $resolved;
        }

        $resolved = $this->resolveCallable($toResolve);
        $this->assertCallable($resolved);

        return $resolved;
    }

    /**
     * Check if string is something in the DIC
     * that's callable or is a class name which has an __invoke() method.
     *
     * @param string $class
     * @param string $method
     * @return callable
     *
     * @throws \RuntimeException if the callable does not exist
     */
    protected function resolveCallable(string $class, ?string $method = '__invoke')
    {
        $method = $method ?: '__invoke';
        $class = $class{0} === '\\' ? $class : $this->namespace . $class;
        if (!$this->container->has($class)) {
            if (!class_exists($class)) {
                throw new RuntimeException(sprintf('Callable %s does not exist', $class));
            }

            $this->container[$class] = new $class($this->container);
        }

        return [$this->container->get($class), $method];
    }

    /**
     * @param Callable $callable
     *
     * @throws \RuntimeException if the callable is not resolvable
     */
    protected function assertCallable($callable)
    {
        if (!is_callable($callable)) {
            throw new RuntimeException(sprintf(
                '%s is not resolvable',
                is_array($callable) || is_object($callable) ? json_encode($callable) : $callable
            ));
        }
    }
}
