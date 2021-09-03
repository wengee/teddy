<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Interfaces;

interface DefinitionInterface
{
    public function setShared(bool $shared = true): DefinitionInterface;

    public function isShared(): bool;

    public function addArgument($arg): DefinitionInterface;

    public function addArguments(array $args): DefinitionInterface;

    public function resolve();

    public function resolveNew();
}
