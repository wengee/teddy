<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 16:43:05 +0800
 */

namespace Teddy\Queue;

use Teddy\Task;

class Queue extends BaseQueue
{
    public function push(Task $task): void
    {
        $redis = app('redis')->connection($this->redis);
        $redis->rPush($this->key, serialize($task));
        $redis->publish($this->channelKey, 'Task['.get_class($task).'] is publish.');
    }
}
