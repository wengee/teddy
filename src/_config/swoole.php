<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 16:08:21 +0800
 */

use Teddy\Config\Repository;

return [
    'http' => new Repository([
        'count'     => 1,
        'host'      => '127.0.0.1',
        'port'      => 9500,
        'ssl'       => false,
        'reusePort' => true,
        'options'   => new Repository([]),
    ]),

    'websocket' => new Repository([
        'count'     => 0,
        'path'      => null,
        'host'      => null,
        'port'      => 0,
        'ssl'       => false,
        'reusePort' => true,
        'options'   => new Repository([]),
        'handler'   => new Repository(null, Repository::DATA_AS_RAW | Repository::DATA_PROTECTED),
    ]),

    'task' => new Repository([
        'count' => 1,
    ]),
];
