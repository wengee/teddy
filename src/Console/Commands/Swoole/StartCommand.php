<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-13 20:22:15 +0800
 */

namespace Teddy\Console\Commands\Swoole;

use Teddy\Application as TeddyApplication;
use Teddy\Console\Command;
use Teddy\Swoole\Server;

class StartCommand extends Command
{
    protected $name = 'swoole:start';

    protected $description = 'Start swoole server';

    protected function handle(): void
    {
        defined('IN_SWOOLE') || define('IN_SWOOLE', true);

        $now = date('Y-m-d H:i:s');
        $this->info("[{$now}] Starting swoole server...");

        $app = $this->getApplication()->getApp();
        if (!$app || !($app instanceof TeddyApplication)) {
            $this->error("[{$now}] The app is can not start");
        }

        $server = new Server($app);
        $server->setCommand($this)->start();
    }
}
