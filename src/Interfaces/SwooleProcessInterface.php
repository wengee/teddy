<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-11 11:22:54 +0800
 */

namespace Teddy\Interfaces;

interface SwooleProcessInterface
{
    public function isPool(): bool;

    public function getName(): string;

    public function getCount(): int;

    public function enableCoroutine(): bool;

    public function getOptions(): array;

    public function getListen(): string;

    public function start(int $worker);
}
