<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:25:49 +0800
 */

namespace Teddy\Console\Commands\Workerman;

use Teddy\Abstracts\AbstractCommand;
use Teddy\Runtime;
use Teddy\Workerman\Server;
use Workerman\Worker;

abstract class DefaultCommand extends AbstractCommand
{
    protected string $action = '';

    protected array $optionMap = [
        'daemon'     => '-d',
        'gracefully' => '-g',
        'live'       => '-d',
    ];

    protected array $availableLoop = [
        'ev'             => \Workerman\Events\Ev::class,
        'event'          => \Workerman\Events\Event::class,
        'libevent'       => \Workerman\Events\Libevent::class,
        'swoole'         => \Workerman\Events\Swoole::class,
        'react-event'    => \Workerman\Events\React\ExtEventLoop::class,
        'react-libevent' => \Workerman\Events\React\ExtLibEventLoop::class,
    ];

    protected function handle(): void
    {
        Runtime::set(Runtime::WORKERMAN);
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

        (new Server($this->output))->start();
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
