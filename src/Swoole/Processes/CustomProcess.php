<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-17 20:25:18 +0800
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

    public function enableCoroutine(): bool
    {
        if ($this->count > 1) {
            return false;
        }

        return $this->process->getOption('enable_coroutine', true);
    }

    public function handle(int $pWorkerId): void
    {
        if (1 === $this->count) {
            $this->runProcess(0);
        } else {
            $pool = new Pool($this->process->getCount());

            $pool->set($this->process->getOptions() + ['enable_coroutine' => true]);

            $pool->on('workerStart', function (Pool $pool, int $workerId): void {
                $this->runProcess($workerId, $pool->getProcess($workerId));
            });

            $pool->start();
        }
    }

    protected function runProcess(int $workerId, ?Process $process = null): void
    {
        if ($this->count > 1) {
            ProcessUtil::setTitle($this->getName().' ('.$workerId.')');
        }

        $this->process->setWorker($process);

        Process::signal(SIGTERM, function (): void {
            $this->process->onReload();
        });

        $this->process->handle();
    }
}
