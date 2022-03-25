<?php

namespace Teddy\Traits;

use Teddy\Interfaces\TaskInterface;

trait TaskAwareTrait
{
    /** @param array|string|TaskInterface $task */
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
