<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-21 14:17:57 +0800
 */

namespace Teddy\Swoole;

use Swoole\Timer;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\QueueInterface;
use Teddy\Redis\Redis;
use Teddy\Traits\AddTaskTrait;
use Teddy\Traits\ContainerAwareTrait;
use Throwable;

class Queue implements ContainerAwareInterface, QueueInterface
{
    use ContainerAwareTrait;
    use AddTaskTrait;

    protected $queueWaiting = 'queue:waiting:';

    protected $queueDelayed = 'queue:delayed';

    protected $redis;

    protected $redisPrefix;

    protected $retrySeconds = 5;

    protected $maxAttempts = 5;

    protected $subscribeQueue = [];

    protected $subscribeCallback = [];

    protected $brPoping = false;

    public function __construct()
    {
        $options = config('queue', []);

        $this->redis        = $options['redis'] ?? 'default';
        $this->retrySeconds = $options['retrySeconds'];
        $this->maxAttempts  = $options['maxAttempts'];
        $this->redisPrefix  = config('redis.'.$this->redis.'.prefix', '');

        $prefix             = $options['key'] ?? 'queue:';
        $this->queueWaiting = $prefix.'waiting:';
        $this->queueDelayed = $prefix.'delayed';
    }

    public function send(string $queue, $data, int $at = 0): void
    {
        static $num = 0;
        $now        = time();
        $id         = $now.'.'.(++$num);
        $package    = [
            'id'       => $id,
            'time'     => $now,
            'at'       => $at,
            'attempts' => 0,
            'queue'    => $queue,
            'data'     => $data,
        ];

        if ($at > 0) {
            $this->redis()->zAdd($this->queueDelayed, $at, $package);
        } else {
            $this->redis()->lPush($this->queueWaiting.$queue, $package);
        }
    }

    /**
     * @param string|string[] $queue
     */
    public function subscribe($queue, callable $callback): void
    {
        $queue = is_array($queue) ? $queue : [$queue];
        foreach ($queue as $q) {
            $this->subscribeQueue[] = $this->queueWaiting.$q;

            $this->subscribeCallback[$this->redisPrefix.$this->queueWaiting.$q] = $callback;
        }

        $this->process();
    }

    protected function processDelayQueue(): void
    {
        static $retryTimer;
        if ($retryTimer) {
            return;
        }

        $retryTimer = Timer::tick(1000, function (): void {
            $now     = time();
            $options = ['LIMIT', 0, 50];
            $items   = $this->redis()->zRevRangeByScore($this->queueDelayed, (string) $now, '-inf', $options);
            foreach ($items as $package) {
                $result = $this->redis()->zRem($this->queueDelayed, $package);
                if (1 !== $result) {
                    continue;
                }

                $this->redis()->lPush($this->queueWaiting.$package['queue'], $package);
            }
        });
    }

    protected function process(): void
    {
        $this->processDelayQueue();
        if (!$this->subscribeQueue || $this->brPoping) {
            return;
        }

        $callback = function () use (&$callback): void {
            $this->brPoping = true;
            $data           = $this->redis()->brPop($this->subscribeQueue, 1);

            if ($data) {
                $this->brPoping = false;
                $redisKey       = $data[0];
                $package        = $data[1];
                $func           = $this->subscribeCallback[$redisKey];

                try {
                    call_user_func($func, $package['data']);
                } catch (Throwable $e) {
                    log_exception($e);

                    if (++$package['attempts'] >= $this->maxAttempts) {
                        $package['error'] = (string) $e;
                        $this->fail($package);
                    } else {
                        $this->retry($package);
                    }
                }
            }

            if ($this->subscribeQueue) {
                Timer::after(1, $callback);
            }
        };

        $callback();
    }

    protected function retry($package): void
    {
        $delay = time() + $this->retrySeconds * ($package['attempts']);
        $this->redis()->zAdd($this->queueDelayed, $delay, $package);
    }

    protected function fail($package): void
    {
        log_message(null, 'ERROR', 'Queue fail: '.json_encode($package));
    }

    protected function redis(): Redis
    {
        static $client;
        if (null === $client) {
            $client = $this->getContainer()
                ->get('redis')
                ->connection($this->redis)
            ;
        }

        return $client;
    }
}
