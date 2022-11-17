<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-17 20:26:37 +0800
 */

use App\WebsocketHandler;

return [
    'http' => [
        'count' => 1,
    ],

    'websocket' => [
        'count'   => 1,
        'host'    => '127.0.0.1',
        'port'    => 9600,
        'handler' => WebsocketHandler::class,
    ],

    'task' => [
        'count' => 1,
    ],
];
