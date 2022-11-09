<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 16:01:18 +0800
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
        'host'       => '127.0.0.1',
        'port'       => null,
        'sock'       => null,
        'count'      => 1,
        'reusePort'  => false,
        'reloadable' => true,
    ]),

    'stdoutFile' => null,
    'pidFile'    => null,
    'logFile'    => null,
    'daemonize'  => false,
    'loop'       => null,
];
