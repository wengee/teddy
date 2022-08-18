<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-18 17:41:54 +0800
 */

namespace Teddy\Interfaces;

interface QueueInterface
{
    public function send(string $queue, $data, int $at = 0);

    /**
     * @param string|string[] $queue
     */
    public function subscribe($queue, callable $callback);
}
