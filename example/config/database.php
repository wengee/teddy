<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-11-30 10:54:25 +0800
 */

use Teddy\Config\Repository;

return [
    'logger' => 'file',

    'default' => new Repository([
        'driver'   => 'mysql',
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'test',
        'user'     => 'root',
        'password' => 'toor',
        'charset'  => 'utf8mb4',
        'options'  => [],
        'pool'     => [
            'maxConnections' => 2,
        ],
    ]),
];
