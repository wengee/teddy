<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 10:15:52 +0800
 */

namespace Teddy\Abstracts;

use Swoole\Http\Server;
use Swoole\Process;
use Teddy\Interfaces\ProcessInterface;

abstract class AbstractProcess implements ProcessInterface
{
    protected $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function enableCoroutine(): bool
    {
        return true;
    }

    public function onReload(Server $swoole, Process $process)
    {
        $process->exit(0);
    }

    abstract public function handle(Server $swoole, Process $process);
}
