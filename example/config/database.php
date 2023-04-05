<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-05 09:56:55 +0800
 */

use Teddy\Config\Repository;

return [
    'logger' => 'file',

    'default' => new Repository([
        'driver'   => 'sqlite',
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => RESOURCES_PATH.'test.db',
        'user'     => 'root',
        'password' => 'toor',
        'charset'  => 'utf8mb4',
        'options'  => [],
        'pool'     => [
            'maxConnections' => 10,
        ],
    ]),
];
