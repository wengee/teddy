<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-26 11:40:19 +0800
 */

namespace Teddy\Interfaces;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public function bind(string $id, $concrete): ContainerInterface;

    public function instance(string $id, $object): ContainerInterface;

    public function alias(string $id, string $alias): ContainerInterface;

    public function make(string $id, ?array $parameters = null);
}
