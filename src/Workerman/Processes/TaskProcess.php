<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-21 17:27:57 +0800
 */

namespace Teddy\Workerman\Processes;

use Teddy\Abstracts\AbstractProcess;
use Teddy\Application;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Traits\TaskAwareTrait;
use Teddy\Workerman\Queue;
use Workerman\Timer;
use Workerman\Worker;

class TaskProcess extends AbstractProcess implements ProcessInterface
{
    use TaskAwareTrait;

    /** @var Application */
    protected $app;

    protected $name = 'task';

    protected $consumer = true;

    protected $crontab = [];

    protected $queue;

    protected $serverName;

    protected $timerId;

    public function __construct(Application $app, array $options, array $extra = [])
    {
        $this->app     = $app;
        $this->options = $options;

        $this->crontab    = $extra['crontab'] ?? [];
        $this->serverName = $extra['serverName'] ?? null;

        $this->consumer = $options['consumer'] ?? true;
        if ($options['queue']) {
            $this->queue = new Queue($options['queue']);
        }
    }

    /** @param null|array|bool|int|string $extra */
    public function send(string $className, array $args = [], $extra = null): void
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
                $local      = true;
                $serverName = $this->serverName;
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

        if ($this->queue) {
            if ($local && $this->serverName && $this->consumer) {
                $this->queue->send($this->serverName, [$className, $args], $at);
            } else {
                $this->queue->send($this->consumer ? $serverName : 'any', [$className, $args], $at);
            }
        }

        run_hook('workerman:task:afterSend', [
            'className' => $className,
            'args'      => $args,
            'extra'     => $extra,
        ]);
    }

    public function onWorkerStart(Worker $worker): void
    {
        run_hook('workerman:task:beforeWorkerStart', ['worker' => $worker]);

        if ($this->queue) {
            if ($this->consumer) {
                $channels = ['any'];
                if ($this->serverName) {
                    $channels[] = $this->serverName;
                }

                $this->queue->subscribe($channels, function ($data): void {
                    $this->runTask($data);
                });
            }

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