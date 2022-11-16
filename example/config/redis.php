<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-16 22:01:15 +0800
 */

use Teddy\Config\Repository;

return [
    'default' => new Repository([
        'host'   => '127.0.0.1',
        'port'   => 6379,
        'prefix' => 'example:',
        'pool'   => [
            'maxConnections' => 10,
        ],
    ]),
];
