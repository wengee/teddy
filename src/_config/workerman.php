<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 14:26:25 +0800
 */

use Teddy\Config\Repository;

return [
    'http' => new Repository([
        'host'       => '127.0.0.1',
        'port'       => 9500,
        'count'      => 1,
        'reusePort'  => false,
        'reloadable' => true,
    ]),

    'websocket' => new Repository([
        'host'       => '127.0.0.1',
        'port'       => 9600,
        'count'      => 0,
        'reusePort'  => false,
        'reloadable' => true,
        'handler'    => new Repository(null, Repository::DATA_AS_RAW | Repository::DATA_PROTECTED),
    ]),

    'task' => new Repository([
        'count' => 0,
    ]),

    'stdoutFile' => null,
    'pidFile'    => null,
    'logFile'    => null,
    'daemonize'  => false,
    'loop'       => null,
];
