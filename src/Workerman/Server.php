<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 23:24:58 +0800
 */

namespace Teddy\Workerman;

use Teddy\Application;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\QueueInterface;
use Teddy\Interfaces\ServerInterface;
use Teddy\Workerman\Processes\HttpProcess;
use Teddy\Workerman\Processes\TaskProcess;
use Teddy\Workerman\Processes\WebsocketProcess;

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
     * @var ProcessInterface[]
     */
    protected $processes = [];

    /**
     * @var HttpProcess
     */
    protected $httpProcess;

    /**
     * @var WebsocketProcess
     */
    protected $websocketProcess;

    /**
     * @var TaskProcess
     */
    protected $taskProcess;

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
        foreach ($this->processes as $process) {
            Util::startWorker($process);
        }

        Util::runAll();
    }

    public function addProcess(ProcessInterface $process): void
    {
        $this->processes[] = $process;
    }

    protected function initialize(): void
    {
        $this->addHttpProcess();
        $this->addWebsocketProcess();
        $this->addTaskProcess();

        $processes = config('process');
        if (is_array($processes) && $processes) {
            $this->addCustomProcesses($processes);
        }
    }

    protected function addHttpProcess(): void
    {
        $options = config('workerman.http');
        if ($options['count'] > 0) {
            $this->addProcess(new HttpProcess($this->app, $options));
        }
    }

    protected function addWebsocketProcess(): void
    {
        $options = config('workerman.websocket');
        if ($options['count'] > 0) {
            $this->addProcess(new WebsocketProcess($this->app, $options));
        }
    }

    protected function addTaskProcess(): void
    {
        $options = config('workerman.task');
        if ($options['count'] > 0) {
            $this->addProcess(new TaskProcess($this->app, $options, [
                'crontab'  => config('crontab'),
                'channels' => config('queue.channels'),
            ]));
        }
    }

    protected function addCustomProcesses(array $processes): void
    {
        foreach ($processes as $key => $item) {
            $className = null;
            $args      = [];

            if (is_integer($key)) {
                if (is_string($item)) {
                    $className = $item;
                } elseif (is_array($item)) {
                    $className = $item['class'] ?? null;
                    $args      = $item['arguments'] ?? [];
                }
            } elseif (is_string($key)) {
                $className = $key;
                if (is_array($item)) {
                    $args = $item;
                }
            }

            if (!$className || !class_exists($className)) {
                continue;
            }

            $args    = is_array($args) ? $args : [$args];
            $process = new $className(...$args);
            if (!($process instanceof ProcessInterface)) {
                continue;
            }

            $count = $process->getOption('count', 0);
            if ($count > 0) {
                $this->addProcess($process);
            }
        }
    }
}
