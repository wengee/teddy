<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-24 15:19:29 +0800
 */

namespace Teddy\Workerman;

use Teddy\Application;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\ServerInterface;
use Teddy\Workerman\Processes\HttpProcess;
use Teddy\Workerman\Processes\TaskProcess;
use Teddy\Workerman\Processes\WebsocketProcess;

class Server implements ServerInterface
{
    /** @var Application */
    protected $app;

    /** @var ContainerInterface */
    protected $container;

    /** @var HttpProcess */
    protected $httpProcess;

    /** @var WebsocketProcess */
    protected $websocketProcess;

    /** @var TaskProcess */
    protected $taskProcess;

    public function __construct(Application $app)
    {
        $this->app       = $app;
        $this->container = $app->getContainer();

        $this->container->addValue('server', $this);
        $this->container->addValue(ServerInterface::class, $this);
    }

    public function start(): void
    {
        $this->httpProcess      = $this->addHttpProcess();
        $this->websocketProcess = $this->addWebsocketProcess();
        $this->taskProcess      = $this->addTaskProcess();

        $processes = config('process');
        if (is_array($processes) && $processes) {
            $this->addCustomProcesses($processes);
        }

        Util::runAll();
    }

    public function addProcess(ProcessInterface $process): ProcessInterface
    {
        Util::startWorker($process);

        return $process;
    }

    /** @param null|array|bool|int $extra */
    public function addTask(string $className, array $args = [], $extra = null): void
    {
        if ($this->taskProcess) {
            $this->taskProcess->send($className, $args, $extra);
        }
    }

    protected function addHttpProcess(): ?ProcessInterface
    {
        $options = config('workerman.http');
        if ($options['count'] > 0) {
            return $this->addProcess(new HttpProcess($this->app, $options));
        }

        return null;
    }

    protected function addWebsocketProcess(): ?ProcessInterface
    {
        $options = config('workerman.websocket');
        if ($options['count'] > 0) {
            return $this->addProcess(new WebsocketProcess($this->app, $options));
        }

        return null;
    }

    protected function addTaskProcess(): ?ProcessInterface
    {
        $options = config('workerman.task');
        if ($options['count'] > 0) {
            return $this->addProcess(new TaskProcess($this->app, $options, [
                'crontab' => config('crontab'),
                'server'  => config('app.server'),
            ]));
        }

        return null;
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
                    $args      = $item['parameters'] ?? [];
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
