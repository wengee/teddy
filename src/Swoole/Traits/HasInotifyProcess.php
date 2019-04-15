<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-15 17:33:42 +0800
 */
namespace Teddy\Swoole\Traits;

use Swoole\Process;
use Teddy\Swoole\Inotify;
use Teddy\Swoole\Server;

trait HasInotifyProcess
{
    protected function addInotifyProcess(Server $server, array $config)
    {
        if (empty($config['enable'])) {
            return;
        }

        if (!extension_loaded('inotify')) {
            return;
        }

        $watcher = function () use ($server, $config) {
            $server->setProcessTitle('inotify process');

            $inotify = new Inotify($server->getBasePath(), $config);
            $inotify->setHandler(function ($event) use ($server) {
                $server->getSwoole()->reload();
                // $server->reloadTimerProcess();
            });
            $inotify->watch()->start();
        };

        $inotifyProcess = new Process($watcher, false, 0);
        if ($server->getSwoole()->addProcess($inotifyProcess)) {
            return $inotifyProcess;
        }
    }
}
