<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-21 17:32:31 +0800
 */

namespace Teddy\Interfaces;

interface ServerInterface
{
    public function getServerName(): string;

    public function addProcess(ProcessInterface $process);

    /** @param null|array|bool|int|string $extra */
    public function addTask(string $className, array $args = [], $extra = null);
}