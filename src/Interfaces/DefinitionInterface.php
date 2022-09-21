<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-21 16:03:09 +0800
 */

namespace Teddy\Interfaces;

interface DefinitionInterface
{
    public function setShared(bool $shared = true): DefinitionInterface;

    public function isShared(): bool;

    public function addArgument($arg): DefinitionInterface;

    public function addCollectionArgument(array $args): DefinitionInterface;

    public function addLiteralArgument($value, string $type = null): DefinitionInterface;

    public function addArguments(array $args): DefinitionInterface;

    public function resolve();

    public function resolveNew();
}
