<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 22:36:12 +0800
 */

namespace Teddy\Traits;

trait AddTaskTrait
{
    public function addTask(string $className, array $args = [], array $options = []): void
    {
        $queue = $options['queue'] ?? 'default';
        $delay = intval($options['delay'] ?? 0);
        if ($delay > 0) {
            $at = time() + $delay;
        } else {
            $at = intval($options['at'] ?? 0);
        }

        $this->send($queue, [$className, $args], $at);
    }
}
