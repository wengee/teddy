<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 21:03:08 +0800
 */

namespace Teddy\Swoole;

use function Swoole\Coroutine\run;
use Swoole\Process\Manager as ProcessManager;
use Swoole\Process\Pool;
use Swoole\Runtime;
use Teddy\Abstracts\AbstractCommand;
use Teddy\Application;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\QueueInterface;
use Teddy\Interfaces\ServerInterface;
use Teddy\Swoole\Processes\CustomProcess;
use Teddy\Swoole\Processes\HttpProcess;
use Teddy\Swoole\Processes\TaskProcess;
use Teddy\Swoole\Processes\WebsocketProcess;
use Teddy\Swoole\ProcessInterface as SwooleProcessInterface;
use Teddy\Utils\Process;

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

    /**
     * @var null|AbstractCommand
     */
    protected $command;

    /**
     * @var array
     */
    protected $message = [];

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

    public function setCommand(AbstractCommand $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function start(): void
    {
        $coroutineFlags = (int) config('swoole.coroutineFlags');
        Runtime::enableCoroutine($coroutineFlags);

        if ($this->command) {
            $this->command->table(['process', 'listen', 'count'], $this->message);
        }

        if (1 === count($this->processes)) {
            $process = $this->processes[0];
            if (1 === $process->getCount()) {
                run(function () use ($process): void {
                    $process->start(0);
                });

                return;
            }
        }

        Process::setTitle('master process');

        $pm = new ProcessManager();
        foreach ($this->processes as $process) {
            $pm->addBatch(
                $process->isPool() ? 1 : $process->getCount(),
                function (Pool $pool, int $workerId) use ($process): void {
                    $process->start($workerId);
                },
                $process->enableCoroutine()
            );
        }

        $pm->start();
    }

    public function addProcess(ProcessInterface $process): void
    {
        $this->addSwooleProcess(new CustomProcess($process));
    }

    protected function addSwooleProcess(SwooleProcessInterface $process): void
    {
        $this->processes[] = $process;
        $this->message[]   = [$process->getName(), $process->getListen(), $process->getCount()];
    }

    protected function addServerProcess(): void
    {
        $httpOptions = config('swoole.http');
        $wsOptions   = config('swoole.websocket');
        if ($wsOptions['count'] > 0) {
            if ((!$wsOptions['host'] || !$wsOptions['port']) && $httpOptions['count'] > 0) {
                $this->addSwooleProcess(new WebsocketProcess($this->app, [
                    ...$httpOptions,
                    'path'        => $wsOptions['path'],
                    'httpProcess' => true,
                ]));

                return;
            }

            $this->addSwooleProcess(new WebsocketProcess($this->app, $wsOptions));
        }

        if ($httpOptions['count'] > 0) {
            $this->addSwooleProcess(new HttpProcess($this->app, $httpOptions));
        }
    }

    protected function addTaskProcess(): void
    {
        $options = config('swoole.task');
        if ($options['count'] > 0) {
            $this->addSwooleProcess(new TaskProcess($this->app, $options, [
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

    protected function initialize(): void
    {
        $this->addServerProcess();
        $this->addTaskProcess();

        $processes = config('process');
        if (is_array($processes) && $processes) {
            $this->addCustomProcesses($processes);
        }
    }
}
