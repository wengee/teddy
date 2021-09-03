<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Interfaces;

use Swoole\Http\Server;
use Swoole\Process;

interface ProcessInterface
{
    public function getName(): string;

    public function enableCoroutine(): bool;

    public function handle(Server $swoole, Process $process);

    public function onReload(Server $swoole, Process $process);
}
