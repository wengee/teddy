<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-12 15:56:32 +0800
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
