<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-07-10 16:20:31 +0800
 */

namespace Teddy\Interfaces;

interface ServerInterface
{
    public function start();

    public function getStartTime(): int;

    public function addProcess(ProcessInterface $process);

    public function stats(): array;
}
