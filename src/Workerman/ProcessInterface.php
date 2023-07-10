<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-07-10 16:45:58 +0800
 */

namespace Teddy\Workerman;

interface ProcessInterface
{
    public function getName(): string;

    public function getCount(): int;

    public function getListen(): string;

    public function getContext(): array;

    public function getOptions(): array;
}
