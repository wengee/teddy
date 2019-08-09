<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-08 17:55:36 +0800
 */

return [
    'default' => [
        'engine'    => 'mysql',
        'host'      => env('DB_HOST', '127.0.0.1'),
        'port'      => (int) env('DB_PORT', 3306),
        'name'      => env('DB_NAME', 'test'),
        'user'      => env('DB_USER', 'root'),
        'password'  => env('DB_PASSWORD', 'toor'),
        'charset'   => env('DB_CHARSET', 'utf8mb4'),
        'options'   => [],
    ],
];
