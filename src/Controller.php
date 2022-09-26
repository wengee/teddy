<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-26 15:58:07 +0800
 */

namespace Teddy;

use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Traits\ContainerAwareTrait;

abstract class Controller implements ContainerAwareInterface
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
