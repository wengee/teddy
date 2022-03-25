<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-23 16:25:24 +0800
 */

namespace Teddy\Interfaces;

interface ProcessInterface
{
    public function getName(): string;

    public function getListen(): string;

    public function getContext(): array;

    public function getOptions(): array;

    public function getOption(string $name, $default = null);

    public function setWorker($worker);

    public function getWorker();
}
