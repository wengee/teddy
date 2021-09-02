<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-02 14:25:57 +0800
 */

use Teddy\Config\Repository;

return [
    'default' => new Repository([
        'host'   => '127.0.0.1',
        'port'   => 6379,
        'prefix' => 'example:',
        'pool'   => [
            'maxConnections' => 2,
        ],
    ]),
];
