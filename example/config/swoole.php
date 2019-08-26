<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-26 16:41:43 +0800
 */

return [
    'host' => env('SWOOLE_HOST', '0.0.0.0'),
    'port' => (int) env('SWOOLE_PORT', 9509),

    'websocket' => [
        'enable' => false,
        'handler' => App\WebsocketHandler::class,
    ],

    'schedule' => [
        ['*/2 * * * * 0', App\Tasks\Demo::class],
    ],

    'processes' => [],

    'options' => [
        'dispatch_mode' => 1,
        'worker_num' => 1,
        'task_worker_num' => 1,
    ],
];
