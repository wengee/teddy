<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 20:39:43 +0800
 */

namespace Teddy\Swoole;

interface ProcessInterface
{
    public function isPool(): bool;

    public function getName(): string;

    public function getCount(): int;

    public function enableCoroutine(): bool;

    public function getOptions(): array;

    public function getListen(): string;

    public function start(int $worker);
}
