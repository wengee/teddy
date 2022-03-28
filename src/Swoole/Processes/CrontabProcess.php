<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-28 17:28:05 +0800
 */

namespace Teddy\Swoole\Processes;

use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Timer;
use Teddy\Abstracts\AbstractProcess;
use Teddy\Interfaces\ProcessInterface;

class CrontabProcess extends AbstractProcess implements ProcessInterface
{
    protected $name = 'crontab process';

    protected $timerId;

    protected $list = [];

    public function __construct(array $list)
    {
        $this->list    = $list;
        $this->options = ['coroutine' => true];
    }

    public function handle(Server $swoole, Process $process): void
    {
        $this->timerId = Timer::tick(1000, function (): void {
            app('crontab')->run();
        });
    }

    public function onReload(Server $swoole, Process $process): void
    {
        if (null !== $this->timerId) {
            Timer::clear($this->timerId);
        }

        $process->exit(0);
    }
}
