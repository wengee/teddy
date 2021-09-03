<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

use Teddy\Config\Repository;

$cpuNum  = function_exists('swoole_cpu_num') ? swoole_cpu_num() : 1;

return [
    'host'      => '127.0.0.1',
    'port'      => 9500,

    'websocket' => new Repository([
        'enabled' => false,
        'handler' => new Repository(null, Repository::DATA_AS_RAW | Repository::DATA_PROTECTED),
    ]),

    'schedule'  => new Repository([
        'enabled' => false,
        'list'    => new Repository([], Repository::DATA_AS_LIST | Repository::DATA_PROTECTED),
    ]),

    'queue'     => new Repository([
        'enabled'  => false,
        'consumer' => false,
        'key'      => 'task:queue',
        'redis'    => 'default',
    ]),

    'processes' => new Repository([], Repository::DATA_AS_LIST | Repository::DATA_PROTECTED),

    'tables'    => new Repository([], Repository::DATA_PROTECTED),

    'options'   => new Repository([
        'reactor_num'     => $cpuNum * 2,
        'worker_num'      => $cpuNum * 2,
        'task_worker_num' => $cpuNum * 2,
        'dispatch_mode'   => 1,
        'daemonize'       => 0,
        'http_parse_post' => true,
    ]),
];
