<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-30 15:22:13 +0800
 */

namespace Teddy;

use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Traits\ContainerAwareTrait;

abstract class Controller implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    final public function __construct()
    {
        $this->initialize();
    }

    final public function __get($name)
    {
        return app($name);
    }

    public function initialize(): void
    {
    }
}
