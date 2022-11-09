<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 22:30:23 +0800
 */

namespace Teddy\Interfaces;

interface QueueInterface
{
    public function send(string $queue, $data, int $at = 0);

    /**
     * @param string|string[] $queue
     */
    public function subscribe($queue, callable $callback);

    public function addTask(string $className, array $args = [], array $options = []);
}
