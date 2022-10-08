<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-21 15:46:00 +0800
 */

namespace Teddy\Traits;

use Exception;
use Psr\Container\ContainerInterface as PsrContainerInterface;
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
            if (in_array($arg, ['container', ContainerInterface::class, PsrContainerInterface::class])) {
                $newArgs[] = $container;
            } elseif ($arg instanceof LiteralArgumentInterface) {
                $newArgs[] = $arg->getValue();
            } elseif ($arg instanceof CollectionArgumentInterface) {
                if ($container && ($arg instanceof ContainerAwareInterface)) {
                    $arg->setContainer($container);
                }

                $newArgs[] = $arg->getValue();
            } elseif (is_string($arg) && ($container instanceof ContainerInterface)) {
                $newArgs[] = $container->get($arg);
            } else {
                $newArgs[] = $arg;
            }
        }

        return $newArgs;
    }
}
