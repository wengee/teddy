<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 21:03:59 +0800
 */

namespace Teddy\Swoole\Processes;

use Swoole\Process;
use Swoole\Process\Pool;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Swoole\ProcessInterface as SwooleProcessInterface;
use Teddy\Utils\Process as ProcessUtil;

class CustomProcess extends AbstractProcess implements SwooleProcessInterface
{
    protected $isPool = true;

    /**
     * @var ProcessInterface
     */
    protected $process;

    protected $enableCoroutine = false;

    public function __construct(ProcessInterface $process)
    {
        $this->process = $process;
        $this->name    = $process->getName();
        $this->count   = $process->getCount();
    }

    public function handle(int $workerId): void
    {
        $pool = new Pool($this->process->getCount());

        $pool->set($this->process->getOptions() + ['enable_coroutine' => true]);

        $pool->on('workerStart', function (Pool $pool, int $workerId): void {
            ProcessUtil::setTitle($this->getName().' ('.$workerId.')');
            $this->process->setWorker($pool->getProcess($workerId));

            Process::signal(SIGTERM, function (): void {
                $this->process->onReload();
            });

            $this->process->handle();
        });

        $pool->start();
    }
}
