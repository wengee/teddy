<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 20:37:51 +0800
 */

namespace Teddy\Workerman;

interface ProcessInterface
{
    public function getName(): string;

    public function getListen(): string;

    public function getContext(): array;

    public function getOptions(): array;
}
