<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-10-11 14:19:25 +0800
 */

namespace Teddy\Interfaces;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public function add(string $id, $concrete = null, bool $shared = false): DefinitionInterface;

    public function addShared(string $id, $concrete = null): DefinitionInterface;

    public function addValue(string $id, $value): void;

    public function addAlias(string $id, string $alias): void;

    public function remove(string $id): void;

    public function removeAlias(string $id): void;

    public function getNew(string $id);
}
