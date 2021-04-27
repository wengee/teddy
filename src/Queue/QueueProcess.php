<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 17:20:51 +0800
 */

namespace Teddy\Queue;

use Exception;
use Redis;
use Swoole\Http\Server;
use Swoole\Process;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Task;

class QueueProcess extends BaseQueue implements ProcessInterface
{
    protected $busy = false;

    public function getName(): string
    {
        return 'queue process';
    }

    public function enableCoroutine(): bool
    {
        return true;
    }

    public function handle(Server $swoole, Process $process): void
    {
        $this->startProcess();

        START_HANDLE:
        try {
            $this->redis()->subscribe([$this->channelKey], function ($redis, $channel, $msg): void {
                log_message('INFO', 'Queue channel: [%s] %s', $channel, $msg);
                $this->startProcess();
            });
        } catch (Exception $e) {
            log_exception($e);

            goto START_HANDLE;
        }
    }

    public function onReload(Server $swoole, Process $process): void
    {
        $process->exit(0);
    }

    protected function startProcess(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $this->processTaskList();
        } catch (Exception $e) {
            log_exception($e);
        }

        $this->busy = false;
    }

    protected function processTaskList(): void
    {
        while ($data = app('redis')->lPop($this->key)) {
            $task = unserialize($data);
            if ($task instanceof Task) {
                $task->send();
            }
        }
    }

    protected function redis(): Redis
    {
        $redis = app('redis')->connection($this->redis)->getNativeClient();
        $redis->setOption(Redis::OPT_READ_TIMEOUT, -1);

        return $redis;
    }
}
