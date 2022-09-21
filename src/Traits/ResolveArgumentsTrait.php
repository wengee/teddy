<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-21 15:46:00 +0800
 */

namespace Teddy\Traits;

use Exception;
use Teddy\Interfaces\CollectionArgumentInterface;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\LiteralArgumentInterface;

trait ResolveArgumentsTrait
{
    protected function resolveArguments(array $arguments): array
    {
        if (!($this instanceof ContainerAwareInterface)) {
            return $arguments;
        }

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

            if ($arg instanceof CollectionArgumentInterface) {
                if ($container && ($arg instanceof ContainerAwareInterface)) {
                    $arg->setContainer($container);
                }

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
}
