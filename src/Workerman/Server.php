<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-07-10 17:00:11 +0800
 */

namespace Teddy\Workerman;

use Symfony\Component\Console\Output\OutputInterface;
use Teddy\Application;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\QueueInterface;
use Teddy\Interfaces\ServerInterface;
use Teddy\Utils\Workerman;
use Teddy\Workerman\Processes\CustomProcess;
use Teddy\Workerman\Processes\HttpProcess;
use Teddy\Workerman\Processes\TaskProcess;
use Teddy\Workerman\Processes\WebsocketProcess;
use Teddy\Workerman\ProcessInterface as WorkermanProcessInterface;

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
     * @var WorkermanProcessInterface[]
     */
    protected $processes = [];

    /**
     * @var QueueInterface
     */
    protected $queue;

    /**
     * @var null|OutputInterface
     */
    protected $output;

    protected int $startTime = 0;

    public function __construct(?OutputInterface $output = null)
    {
        $this->output    = $output;
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
        if (0 === $this->startTime) {
            $this->startTime = time();
        }

        foreach ($this->processes as $process) {
            Workerman::startWorker($process);
        }

        Workerman::runAll();
    }

    public function getStartTime(): int
    {
        return $this->startTime;
    }

    public function addProcess(ProcessInterface $process): void
    {
        $this->addWorkermanProcess(new CustomProcess($process));
    }

    public function stats(): array
    {
        $workerStats = array_map(function (WorkermanProcessInterface $process) {
            return ['name' => $process->getName(), 'count' => $process->getCount()];
        }, $this->processes);

        return [
            'hostname'       => gethostname(),
            'currentWorkPid' => getmypid(),
            'phpVersion'     => PHP_VERSION,
            'swooleVersion'  => constant('SWOOLE_VERSION'),
            'startTime'      => $this->startTime,

            'workers' => $workerStats,

            'memory' => [
                'usage'          => memory_get_usage(),
                'allotUsage'     => memory_get_usage(true),
                'peakUsage'      => memory_get_peak_usage(),
                'peakAllotUsage' => memory_get_peak_usage(true),
            ],
        ];
    }

    protected function addWorkermanProcess(WorkermanProcessInterface $process): void
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
            $this->addWorkermanProcess(new HttpProcess($this->app, $options));
        }
    }

    protected function addWebsocketProcess(): void
    {
        $options = config('workerman.websocket');
        if ($options['count'] > 0) {
            $this->addWorkermanProcess(new WebsocketProcess($this->app, $options));
        }
    }

    protected function addTaskProcess(): void
    {
        $options = config('workerman.task');
        if ($options['count'] > 0) {
            $this->addWorkermanProcess(new TaskProcess($this->app, $options, [
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

            $count = $process->getCount();
            if ($count > 0) {
                $this->addProcess($process);
            }
        }
    }
}
