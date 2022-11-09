<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 23:25:59 +0800
 */

namespace Teddy\Interfaces;

interface ServerInterface
{
    public function start();

    public function addProcess(ProcessInterface $process);
}
