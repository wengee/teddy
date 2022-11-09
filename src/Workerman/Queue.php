<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 22:37:09 +0800
 */

namespace Teddy\Workerman;

use RuntimeException;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\QueueInterface;
use Teddy\Traits\AddTaskTrait;
use Teddy\Traits\ContainerAwareTrait;
use Throwable;
use Workerman\Redis\Client as Redis;
use Workerman\Timer;

class Queue implements ContainerAwareInterface, QueueInterface
{
    use ContainerAwareTrait;
    use AddTaskTrait;

    protected $queueWaiting = 'queue:waiting:';

    protected $queueDelayed = 'queue:delayed';

    protected $redis;

    protected $redisCfg = [];

    protected $retrySeconds = 5;

    protected $maxAttempts = 5;

    protected $subscribeQueue = [];

    protected $subscribeCallback = [];

    protected $brPoping = false;

    public function __construct()
    {
        $options = config('queue', []);

        $this->redis        = $options['redis'] ?? 'default';
        $this->redisCfg     = config('redis.'.$this->redis, []);
        $this->retrySeconds = $options['retrySeconds'];
        $this->maxAttempts  = $options['maxAttempts'];

        $redisPrefix        = $this->redisCfg['prefix'] ?? '';
        $prefix             = $redisPrefix.($options['key'] ?? 'queue:');
        $this->queueWaiting = $prefix.'waiting:';
        $this->queueDelayed = $prefix.'delayed';
    }

    public function send(string $queue, $data, int $at = 0): void
    {
        static $num = 0;
        $now        = time();
        $id         = $now.'.'.(++$num);
        $packageStr = serialize([
            'id'       => $id,
            'time'     => $now,
            'at'       => $at,
            'attempts' => 0,
            'queue'    => $queue,
            'data'     => $data,
        ]);

        if ($at > 0) {
            $this->redisSend()->zAdd($this->queueDelayed, $at, $packageStr);
        } else {
            $this->redisSend()->lPush($this->queueWaiting.$queue, $packageStr);
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

            $this->subscribeCallback[$this->queueWaiting.$q] = $callback;
        }

        $this->process();
    }

    protected function processDelayQueue(): void
    {
        static $retryTimer;
        if ($retryTimer) {
            return;
        }

        $retryTimer = Timer::add(1, function (): void {
            $now     = time();
            $options = ['LIMIT', 0, 50];
            $this->redisSend()->zRevRangeByScore($this->queueDelayed, $now, '-inf', $options, function ($items): void {
                if (false === $items) {
                    throw new RuntimeException($this->redisSend()->error());
                }

                foreach ($items as $packageStr) {
                    $this->redisSend()->zRem($this->queueDelayed, $packageStr, function ($result) use ($packageStr): void {
                        if (1 !== $result) {
                            return;
                        }

                        $package = unserialize($packageStr);
                        if (!$package || !is_array($package)) {
                            log_message(null, 'ERROR', 'Queue error: '.$packageStr);

                            return;
                        }

                        $this->redisSend()->lPush($this->queueWaiting.$package['queue'], $packageStr);
                    });
                }
            });
        });
    }

    protected function process(): void
    {
        $this->processDelayQueue();
        if (!$this->subscribeQueue || $this->brPoping) {
            return;
        }

        $callback = function ($data) use (&$callback): void {
            if ($data) {
                $this->brPoping = false;
                $redisKey       = $data[0];
                $packageStr     = $data[1];
                $package        = unserialize($packageStr);
                if (!$package || !is_array($package)) {
                    log_message(null, 'ERROR', 'Queue error: '.$packageStr);
                } else {
                    $func = $this->subscribeCallback[$redisKey];

                    try {
                        call_user_func($func, $package['data']);
                    } catch (Throwable $e) {
                        if (++$package['attempts'] > $this->maxAttempts) {
                            $package['error'] = (string) $e;
                            $this->fail($package);
                        } else {
                            $this->retry($package);
                        }
                    }
                }
            }

            if ($this->subscribeQueue) {
                $this->brPoping = true;
                Timer::add(0.001, [$this->redisSubscribe(), 'brPop'], [$this->subscribeQueue, 1, $callback], false);
            }
        };

        $this->brPoping = true;
        $this->redisSubscribe()->brPop($this->subscribeQueue, 1, $callback);
    }

    protected function retry($package): void
    {
        $delay = time() + $this->retrySeconds * ($package['attempts']);
        $this->redisSend()->zAdd($this->queueDelayed, $delay, serialize($package));
    }

    protected function fail($package): void
    {
        log_message(null, 'ERROR', 'Queue fail: '.json_encode($package));
    }

    protected function redisSend(): Redis
    {
        static $redisSend;
        if (null === $redisSend) {
            $cfg       = $this->redisCfg ?: [];
            $host      = $cfg['host'] ?? '127.0.0.1';
            $port      = $cfg['port'] ?? 6379;
            $redisSend = new Redis('redis://'.$host.':'.$port);

            $password = $cfg['password'] ?? null;
            if ($password) {
                $redisSend->auth($password);
            }

            $dbIndex = $cfg['dbIndex'] ?? 0;
            if ($dbIndex >= 0) {
                $redisSend->select($dbIndex);
            }
        }

        return $redisSend;
    }

    protected function redisSubscribe(): Redis
    {
        static $redisSubscribe;
        if (null === $redisSubscribe) {
            $cfg            = $this->redisCfg ?: [];
            $host           = $cfg['host'] ?? '127.0.0.1';
            $port           = $cfg['port'] ?? 6379;
            $redisSubscribe = new Redis('redis://'.$host.':'.$port);

            $password = $cfg['password'] ?? null;
            if ($password) {
                $redisSubscribe->auth($password);
            }

            $dbIndex = $cfg['dbIndex'] ?? 0;
            if ($dbIndex >= 0) {
                $redisSubscribe->select($dbIndex);
            }
        }

        return $redisSubscribe;
    }
}
