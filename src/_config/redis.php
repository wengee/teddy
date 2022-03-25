<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-22 16:59:00 +0800
 */

use Teddy\Config\Repository;

return [
    'default' => new Repository([
        'host'     => '127.0.0.1',
        'port'     => 6379,
        'dbIndex'  => 0,
        'password' => null,
        'prefix'   => 'example:',
        'pool'     => [
            'maxConnections' => 2,
        ],
    ]),
];
