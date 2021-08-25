<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-25 15:51:38 +0800
 */

namespace Teddy\Traits;

use BadMethodCallException;
use Exception;
use Psr\Container\ContainerInterface;
use Teddy\Interfaces\ContainerAwareInterface;

trait ContainerAwareTrait
{
    /**
     * @var ?ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container): ContainerAwareInterface
    {
        $this->container = $container;

        if ($this instanceof ContainerAwareInterface) {
            return $this;
        }

        throw new BadMethodCallException(sprintf(
            'Attempt to use (%s) while not implementing (%s)',
            ContainerAwareTrait::class,
            ContainerAwareInterface::class
        ));
    }

    public function getContainer(): ContainerInterface
    {
        if ($this->container instanceof ContainerInterface) {
            return $this->container;
        }

        throw new Exception('No container implementation has been set.');
    }
}
