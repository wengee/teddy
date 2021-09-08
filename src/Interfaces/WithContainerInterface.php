<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-08 17:36:03 +0800
 */

namespace Teddy\Interfaces;

interface WithContainerInterface
{
    public function __construct(ContainerInterface $container);
}
