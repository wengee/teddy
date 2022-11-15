<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-15 21:03:41 +0800
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
        $this->info("[{$now}] Start swoole server.");

        (new Server($this->output))->start();
    }
}
