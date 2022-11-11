<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-11 16:27:03 +0800
 */

use App\WebsocketHandler;

return [
    'http' => [
        'host'  => '127.0.0.1',
        'port'  => 9500,
        'count' => 1,
    ],

    'websocket' => [
        'count'   => 1,
        'handler' => WebsocketHandler::class,
    ],

    'task' => [
        'count' => 1,
    ],

    'stdoutFile' => null,
    'pidFile'    => runtime_path('runtime/app.pid'),
    'logFile'    => runtime_path('runtime/workerman.log'),
    'daemonize'  => false,
    'loop'       => null,
];
