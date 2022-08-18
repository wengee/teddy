<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-18 17:53:27 +0800
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
     * @var string
     */
    protected $serverName;

    protected $name = 'consumer process';

    protected $key;

    protected $channelKey;

    protected $redis;

    protected $busy = false;

    public function __construct(ContainerInterface $container, string $serverName)
    {
        $this->container  = $container;
        $this->options    = ['coroutine' => true];
        $this->serverName = $this->serverName;
    }

    public function handle(Server $swoole, Process $process): void
    {
        $channels = ['any'];
        if ($this->serverName) {
            $channels[] = $this->serverName;
        }

        $swoole = $this->container->get('swoole');

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
