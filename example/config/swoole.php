<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 16:37:42 +0800
 */

use Swoole\Table;

return [
    'host' => env('SWOOLE_HOST', '0.0.0.0'),
    'port' => (int) env('SWOOLE_PORT', 9509),

    'websocket' => [
        'enable'  => false,
        'handler' => App\WebsocketHandler::class,
    ],

    'schedule' => [
        'enabled' => true,
        'list'    => [
            ['*/2 * * * * 0', App\Tasks\Demo::class],
        ],
    ],

    'queue' => [
        'enabled' => true,
        'key'     => 'task:queue',
        'redis'   => 'default',
    ],

    'processes' => [],

    'tables' => [
        'a' => [
            'size'      => 1024,
            'columns'   => [
                ['name' => 'a', 'type' => Table::TYPE_INT],
                ['name' => 'b', 'type' => Table::TYPE_STRING, 'size' => 10],
            ],
        ],
    ],

    'options' => [
        'dispatch_mode'   => 1,
        'worker_num'      => 1,
        'task_worker_num' => 1,
    ],
];
