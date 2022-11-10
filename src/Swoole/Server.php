<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 23:55:00 +0800
 */

namespace Teddy\Swoole;

use function Swoole\Coroutine\run;
use Swoole\Process\Manager as ProcessManager;
use Swoole\Runtime;
use Teddy\Application;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\QueueInterface;
use Teddy\Interfaces\ServerInterface;
use Teddy\Interfaces\SwooleProcessInterface;
use Teddy\Swoole\Processes\HttpProcess;

class Server implements ServerInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SwooleProcessInterface[]
     */
    protected $processes = [];

    /**
     * @var QueueInterface
     */
    protected $queue;

    public function __construct()
    {
        $this->app       = app();
        $this->container = $this->app->getContainer();

        $this->container->addValue('server', $this);
        $this->container->addValue(ServerInterface::class, $this);

        if (!$this->container->has(QueueInterface::class)) {
            $this->container->add(QueueInterface::class, Queue::class);
        }
        $this->queue = $this->container->get(QueueInterface::class);

        $this->initialize();
    }

    public function start(): void
    {
        $coroutineFlags = (int) config('swoole.coroutineFlags');
        Runtime::enableCoroutine($coroutineFlags);

        if (1 === count($this->processes)) {
            $process = $this->processes[0];
            if (1 === $process->getCount()) {
                run(function () use ($process): void {
                    $process->start();
                });

                return;
            }
        }

        $appName = config('app.name', 'Teddy App');
        Util::setProcessTitle('master process', $appName);

        $pm = new ProcessManager();
        foreach ($this->processes as $process) {
            $pm->addBatch($process->getCount(), function () use ($process): void {
                $process->start();
            }, $process->enableCoroutine());
        }

        $pm->start();
    }

    public function addProcess(ProcessInterface $process): void
    {
    }

    protected function addSwooleProcess(SwooleProcessInterface $process): void
    {
        $this->processes[] = $process;
    }

    protected function addHttpProcess(): void
    {
        $options = config('swoole.http');
        if ($options['count'] > 0) {
            $this->addSwooleProcess(new HttpProcess($this->app, $options));
        }
    }

    protected function initialize(): void
    {
        $this->addHttpProcess();
        // $this->addWebsocketProcess();
        // $this->addTaskProcess();

        // $processes = config('process');
        // if (is_array($processes) && $processes) {
        //     $this->addCustomProcesses($processes);
        // }
    }
}
