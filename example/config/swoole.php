<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-16 22:05:38 +0800
 */

use App\WebsocketHandler;

return [
    'http' => [
        'count' => 0,
    ],

    'websocket' => [
        'count'   => 0,
        'host'    => '127.0.0.1',
        'port'    => 9600,
        'handler' => WebsocketHandler::class,
    ],

    'task' => [
        'count' => 1,
    ],
];
