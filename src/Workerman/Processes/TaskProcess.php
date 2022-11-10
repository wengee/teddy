<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 17:37:58 +0800
 */

namespace Teddy\Workerman\Processes;

use Teddy\Application;
use Teddy\Interfaces\QueueInterface;
use Teddy\Interfaces\WorkermanProcessInterface;
use Teddy\Traits\TaskAwareTrait;
use Workerman\Timer;
use Workerman\Worker;

class TaskProcess extends AbstractWorkermanProcess implements WorkermanProcessInterface
{
    use TaskAwareTrait;

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

    protected $timerId;

    public function __construct(Application $app, array $options, array $extra = [])
    {
        $this->app      = $app;
        $this->options  = $options;
        $this->crontab  = $extra['crontab'] ?? null;
        $this->channels = $extra['channels'] ?? [];

        $this->queue = $app->getContainer()->get(QueueInterface::class);
    }

    public function getName(): string
    {
        return 'task';
    }

    public function onWorkerStart(Worker $worker): void
    {
        run_hook('workerman:task:beforeWorkerStart', ['worker' => $worker]);

        if ($this->queue) {
            $channels = $this->channels ?: ['default'];

            $this->queue->subscribe($channels, function ($data): void {
                $this->runTask($data);
            });

            if ((0 === $worker->id) && $this->crontab) {
                $this->timerId = Timer::add(1, function (): void {
                    app('crontab')->run();
                });
            }
        }

        run_hook('workerman:task:afterWorkerStart', ['worker' => $worker]);
    }

    public function onWorkerReload(Worker $worker): void
    {
        run_hook('workerman:task:beforeWorkerReload', ['worker' => $worker]);

        if ($this->timerId) {
            Timer::del($this->timerId);
        }

        run_hook('workerman:task:afterWorkerReload', ['worker' => $worker]);
    }
}
