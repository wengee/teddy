<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 17:35:59 +0800
 */

return [
    'name' => 'Example',
    'websocket' => [
        'enable' => true,
        'handler' => App\WebsocketHandler::class,
    ],
    'crontab' => [
        ['*/2 * * * * 2', App\Tasks\Demo::class],
    ],

    'dispatch_mode' => 1,
    'worker_num' => 4,
    'task_worker_num' => 1,
];
