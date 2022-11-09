<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 15:57:55 +0800
 */

namespace Teddy\Workerman\Processes;

use Teddy\Abstracts\AbstractProcess;
use Teddy\Application;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\QueueInterface;
use Teddy\Traits\TaskAwareTrait;
use Workerman\Timer;
use Workerman\Worker;

class TaskProcess extends AbstractProcess implements ProcessInterface
{
    use TaskAwareTrait;

    /**
     * @var Application
     */
    protected $app;

    protected $name = 'task';

    protected $crontab = [];

    /**
     * @var QueueInterface
     */
    protected $queue;

    protected $serverName;

    protected $timerId;

    public function __construct(Application $app, array $options, array $extra = [])
    {
        $this->app     = $app;
        $this->options = $options;

        $this->crontab    = $extra['crontab'] ?? null;
        $this->serverName = $extra['serverName'] ?? null;

        $this->queue = $app->getContainer()->get(QueueInterface::class);
    }

    public function onWorkerStart(Worker $worker): void
    {
        run_hook('workerman:task:beforeWorkerStart', ['worker' => $worker]);

        if ($this->queue) {
            $channels = ['any'];
            if ($this->serverName) {
                $channels[] = $this->serverName;
            }

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
