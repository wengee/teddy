<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-25 10:17:16 +0800
 */

namespace Teddy\Interfaces;

interface ServerInterface
{
    public function addProcess(ProcessInterface $process);

    /** @param null|array|bool|int $extra */
    public function addTask(string $className, array $args = [], $extra = null);
}
