<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-08 17:36:18 +0800
 */

namespace Teddy;

use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\WithContainerInterface;
use Teddy\Traits\ContainerAwareTrait;

abstract class Controller implements WithContainerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    final public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->initialize();
    }

    public function initialize(): void
    {
    }
}
