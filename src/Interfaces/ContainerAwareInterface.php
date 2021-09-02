<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-26 11:41:04 +0800
 */

namespace Teddy\Interfaces;

interface ContainerAwareInterface
{
    public function getContainer(): ContainerInterface;

    public function setContainer(ContainerInterface $container): ContainerAwareInterface;
}
