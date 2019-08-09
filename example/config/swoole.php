<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-08 10:17:05 +0800
 */

return [
    'websocket' => [
        'enable' => false,
        'handler' => App\WebsocketHandler::class,
    ],
    'schedule' => [
        ['*/2 * * * * 2', App\Tasks\Demo::class],
    ],

    'dispatch_mode' => 1,
    'worker_num' => 4,
    'task_worker_num' => 1,
];
