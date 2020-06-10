<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-10 11:20:47 +0800
 */

namespace Teddy\Console\Commands;

use Teddy\Console\Command;
use Teddy\Swoole\App;

class ServerStartCommand extends Command
{
    protected $name = 'start';

    protected $description = 'Start teddy server';

    protected function handle(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->info("[{$now}] Starting web server...");

        $app = $this->getApplication()->getApp();
        if ($app && ($app instanceof App)) {
            $app->listen();
        } else {
            $this->error("[{$now}] The app is can not start");
        }
    }
}
