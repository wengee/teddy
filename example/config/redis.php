<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-12 17:12:08 +0800
 */

return [
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'prefix' => env('REDIS_PREFIX', 'example:'),
        'pool' => [
            'maxConnections' => 2,
        ],
    ],
];
