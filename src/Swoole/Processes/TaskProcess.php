<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-11 15:43:42 +0800
 */

namespace Teddy\Swoole\Processes;

use Swoole\Process;
use Swoole\Process\Pool;
use Swoole\Timer;
use Teddy\Application;
use Teddy\Interfaces\QueueInterface;
use Teddy\Interfaces\SwooleProcessInterface;
use Teddy\Swoole\Util;
use Teddy\Traits\TaskAwareTrait;

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

    public function __construct(Application $app, array $options = [], array $extra = [])
    {
        $this->app      = $app;
        $this->count    = $options['count'] ?? 1;
        $this->options  = $options['options'] ?? [];
        $this->crontab  = $extra['crontab'] ?? null;
        $this->channels = $extra['channels'] ?? [];
        $this->queue    = $app->getContainer()->get(QueueInterface::class);
    }

    public function handle(int $workerId): void
    {
        $pool = new Pool($this->count);

        $pool->set($this->getOptions() + ['enable_coroutine' => true]);

        $pool->on('workerStart', function (Pool $pool, int $workerId): void {
            Util::setProcessTitle($this->getName().' ('.$workerId.')');
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
        });

        $pool->start();
    }
}
