<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 15:21:46 +0800
 */

namespace Teddy\Interfaces;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public function add(string $id, $concrete = null): DefinitionInterface;

    public function addShared(string $id, $concrete = null): DefinitionInterface;

    public function addValue(string $id, $value): void;

    public function getNew(string $id);
}
