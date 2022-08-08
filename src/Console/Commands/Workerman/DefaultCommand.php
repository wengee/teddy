<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:28:37 +0800
 */

namespace Teddy\Console\Commands\Workerman;

use Teddy\Application as TeddyApplication;
use Teddy\Console\Command;
use Teddy\Workerman\Server;
use Workerman\Worker;

abstract class DefaultCommand extends Command
{
    protected $action = '';

    /**
     * @var array
     */
    protected $optionMap = [
        'daemon'     => '-d',
        'gracefully' => '-g',
        'live'       => '-d',
    ];

    /**
     * @var array
     */
    protected $availableLoop = [
        'ev'             => \Workerman\Events\Ev::class,
        'event'          => \Workerman\Events\Event::class,
        'libevent'       => \Workerman\Events\Libevent::class,
        'swoole'         => \Workerman\Events\Swoole::class,
        'react-event'    => \Workerman\Events\React\ExtEventLoop::class,
        'react-libevent' => \Workerman\Events\React\ExtLibEventLoop::class,
    ];

    protected function handle(): void
    {
        defined('IN_WORKERMAN') || define('IN_WORKERMAN', true);
        $this->initializeWorker();

        global $argv;

        $action = $this->action ?: $this->argument('action');
        $argv   = ['workerman', $action];

        $options = $this->options();
        foreach ($options as $option => $value) {
            if ($value && isset($this->optionMap[$option])) {
                $argv[] = $this->optionMap[$option];
            }
        }

        $app = $this->getApplication()->getApp();
        if (!$app || !($app instanceof TeddyApplication)) {
            $this->error('app is invalid.');
        }

        $server = new Server($app);
        $server->start();
    }

    protected function initializeWorker(): void
    {
        $loop = config('workerman.loop');
        if (null === $loop) {
            if (extension_loaded('event')) {
                $loop = 'event';
            } elseif (extension_loaded('ev')) {
                $loop = 'ev';
            } elseif (extension_loaded('swoole')) {
                $loop = 'swoole';
            }
        }

        if ($loop && isset($this->availableLoop[$loop])) {
            Worker::$eventLoopClass = $this->availableLoop[$loop];
        }

        if ($stdoutFile = config('workerman.stdoutFile')) {
            Worker::$stdoutFile = $stdoutFile;
        }

        Worker::$pidFile      = config('workerman.pidFile', '');
        Worker::$logFile      = config('workerman.logFile', '');
        Worker::$daemonize    = config('workerman.daemonize', false);
        Worker::$processTitle = config('app.name', 'Teddy App');
    }
}
