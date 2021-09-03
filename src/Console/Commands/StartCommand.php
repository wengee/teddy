<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Console\Commands;

use Teddy\Application as TeddyApplication;
use Teddy\Console\Command;
use Teddy\Swoole\Server;

class StartCommand extends Command
{
    protected $name = 'start';

    protected $description = 'Start swoole server';

    protected function handle(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->info("[{$now}] Starting swoole server...");

        $app = $this->getApplication()->getApp();
        if ($app && ($app instanceof TeddyApplication)) {
            $server = new Server($app);
            $server->setCommand($this)->start();
        } else {
            $this->error("[{$now}] The app is can not start");
        }
    }
}
