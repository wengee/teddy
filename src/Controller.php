<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:55:11 +0800
 */

namespace Teddy;

use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\ControllerInterface;
use Teddy\Traits\ContainerAwareTrait;

abstract class Controller implements ControllerInterface, ContainerAwareInterface
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
