<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 16:26:04 +0800
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
     * @var string
     */
    protected $serverName;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ContainerInterface
     */
    protected $container;

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
        $this->app        = app();
        $this->container  = $this->app->getContainer();
        $this->serverName = config('app.server') ?: php_uname('n');

        $this->container->addValue('server', $this);
        $this->container->addValue(ServerInterface::class, $this);

        if (!$this->container->has(QueueInterface::class)) {
            $this->container->add(QueueInterface::class, Queue::class);
        }
        $this->queue = $this->container->get(QueueInterface::class);
    }

    public function getServerName(): string
    {
        return $this->serverName;
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

    /**
     * @param null|array|bool|int|string $extra
     */
    public function addTask(string $className, array $args = [], $extra = null): void
    {
        run_hook('workerman:task:beforeSend', [
            'className' => $className,
            'args'      => $args,
            'extra'     => $extra,
        ]);

        $local      = true;
        $at         = 0;
        $serverName = 'any';
        if (is_bool($extra)) {
            $local = $extra;
        } elseif (is_int($extra)) {
            $at = $extra;
        } elseif (is_string($extra)) {
            if ('local' === $extra) {
                $local = true;
            } else {
                $local      = false;
                $serverName = $extra;
            }
        } elseif (is_array($extra)) {
            if (isset($extra['local'])) {
                $local = $extra['local'];
            }

            if (isset($extra['at'])) {
                $at = (int) $extra['at'];
            } elseif (isset($extra['delay'])) {
                $at = time() + intval($extra['delay']);
            }
        }

        if ($local) {
            $this->queue->send($this->taskProcess ? $this->serverName : 'any', [$className, $args], $at);
        } else {
            $this->queue->send($serverName, [$className, $args], $at);
        }

        run_hook('workerman:task:afterSend', [
            'className' => $className,
            'args'      => $args,
            'extra'     => $extra,
        ]);
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
                'crontab'    => config('crontab'),
                'serverName' => $this->serverName,
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
