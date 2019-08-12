<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-12 17:44:40 +0800
 */

return [
    'host' => env('SWOOLE_HOST', '0.0.0.0'),
    'port' => (int) env('SWOOLE_PORT', 9509),

    'websocket' => [
        'enable' => false,
        'handler' => App\WebsocketHandler::class,
    ],
    'schedule' => [
        ['*/2 * * * * 2', App\Tasks\Demo::class],
    ],

    'dispatch_mode' => 1,
    'worker_num' => 1,
    'task_worker_num' => 1,
];
