<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 23:37:04 +0800
 */

namespace Teddy\Console\Commands\Swoole;

use Teddy\Abstracts\AbstractCommand;
use Teddy\Runtime;
use Teddy\Swoole\Server;

class StartCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('swoole:start')
            ->setDescription('Start swoole server')
        ;
    }

    protected function handle(): void
    {
        Runtime::set(Runtime::SWOOLE);

        $now = date('Y-m-d H:i:s');
        $this->info("[{$now}] Starting swoole server...");

        $server = new Server();
        $server->setCommand($this)->start();
    }
}
