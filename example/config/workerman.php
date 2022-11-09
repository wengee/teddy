<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 16:26:28 +0800
 */

return [
    'http' => [
        'host'       => '127.0.0.1',
        'port'       => 9500,
        'count'      => 1,
        'reusePort'  => false,
        'reloadable' => true,
    ],

    'task' => [
        'host'       => '127.0.0.1',
        'port'       => null,
        'sock'       => null,
        'count'      => 1,
        'reusePort'  => false,
        'reloadable' => true,
    ],

    'stdoutFile' => null,
    'pidFile'    => runtime_path('runtime/app.pid'),
    'logFile'    => runtime_path('runtime/workerman.log'),
    'daemonize'  => false,
    'loop'       => null,
];
