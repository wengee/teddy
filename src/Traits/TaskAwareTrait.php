<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:39:05 +0800
 */

namespace Teddy\Traits;

use Teddy\Interfaces\TaskInterface;

trait TaskAwareTrait
{
    /**
     * @param array|string|TaskInterface $task
     */
    protected function runTask($task): void
    {
        $className = null;
        $args      = [];
        if (is_string($task)) {
            $className = $task;
        } elseif (is_array($task)) {
            $className = $task[0] ?? null;
            $args      = $task[1] ?? [];
        }

        if ($className) {
            $task = new $className(...$args);
        }

        if ($task instanceof TaskInterface) {
            safe_call([$task, 'run']);
        }
    }
}
