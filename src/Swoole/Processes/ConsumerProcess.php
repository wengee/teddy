<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 22:46:15 +0800
 */

namespace Teddy\Swoole\Processes;

use Swoole\Http\Server;
use Swoole\Process;
use Teddy\Abstracts\AbstractProcess;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\QueueInterface;

class ConsumerProcess extends AbstractProcess implements ProcessInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $channels = [];

    protected $name = 'consumer process';

    protected $key;

    protected $channelKey;

    protected $redis;

    protected $busy = false;

    public function __construct(ContainerInterface $container, ?array $channels = [])
    {
        $this->container = $container;
        $this->options   = ['coroutine' => true];
        $this->channels  = $channels;
    }

    public function handle(Server $swoole, Process $process): void
    {
        $channels = $this->channels ?: ['default'];
        $swoole   = $this->container->get('swoole');

        /**
         * @var QueueInterface
         */
        $queue = $this->container->get(QueueInterface::class);
        $queue->subscribe($channels, function ($data) use ($swoole): void {
            $swoole->task($data);
        });
    }

    public function onReload(Server $swoole, Process $process): void
    {
        $process->exit(0);
    }
}
