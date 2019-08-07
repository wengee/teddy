<?php

namespace Teddy\Interfaces;

use Swoole\Http\Server;
use Swoole\Process;

interface ProcessInterface
{
    public function getName(): string;

    public function handle(Server $swoole, Process $process);

    public function onReload(Server $swoole, Process $process);
}
