<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:39:38 +0800
 */

namespace Teddy\Interfaces;

interface ControllerInterface
{
    public function __construct(ContainerInterface $container);
}
