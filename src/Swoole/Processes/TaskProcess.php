<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2024-12-06 09:48:34 +0800
 */

namespace Teddy\Swoole\Processes;

use Swoole\Process;
use Swoole\Process\Pool;
use Swoole\Timer;
use Teddy\Application;
use Teddy\Interfaces\QueueInterface;
use Teddy\Swoole\ProcessInterface as SwooleProcessInterface;
use Teddy\Traits\TaskAwareTrait;
use Teddy\Utils\Process as ProcessUtil;

class TaskProcess extends AbstractProcess implements SwooleProcessInterface
{
    use TaskAwareTrait;

    protected $isPool = true;

    protected $name = 'task';

    protected $enableCoroutine = false;

    /**
     * @var Application
     */
    protected $app;

    protected $crontab = [];

    /**
     * @var QueueInterface
     */
    protected $queue;

    /**
     * @var array
     */
    protected $channels = [];

    /**
     * @var int
     */
    protected $timerId;

    public function __construct(Application $app, array $options = [], array $extra = [])
    {
        $this->app      = $app;
        $this->count    = $options['count'] ?? 1;
        $this->options  = $options['options'] ?? [];
        $this->crontab  = $extra['crontab'] ?? null;
        $this->channels = $extra['channels'] ?? [];
        $this->queue    = $app->getContainer()->get(QueueInterface::class);

        $this->enableCoroutine = 1 === $this->count;
    }

    public function handle(int $pWorkerId): void
    {
        if (1 === $this->count) {
            $this->runProcess(0);
        } else {
            $pool = new Pool($this->count);

            $pool->set($this->getOptions() + ['enable_coroutine' => true]);

            $pool->on('workerStart', function (Pool $pool, int $workerId): void {
                $this->runProcess($workerId);
            });

            $pool->start();
        }
    }

    protected function runProcess(int $workerId): void
    {
        if ($this->count > 1) {
            ProcessUtil::setTitle($this->getName().' ('.$workerId.')');
        }

        if ($this->queue) {
            $channels = $this->channels ?: ['default'];

            $this->queue->subscribe($channels, function ($data): void {
                $this->runTask($data);
            });
        }

        if ((0 === $workerId) && $this->crontab) {
            $this->timerId = Timer::tick(1000, function (): void {
                app('crontab')->run();
            });

            Process::signal(SIGTERM, function (): void {
                Timer::clear($this->timerId);
            });
        }
    }
}
