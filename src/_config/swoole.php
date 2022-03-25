<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-24 16:10:22 +0800
 */

use Teddy\Config\Repository;

$cpuNum  = function_exists('swoole_cpu_num') ? swoole_cpu_num() : 1;

return [
    'host'     => '127.0.0.1',
    'port'     => 9500,
    'crontab'  => true,
    'consumer' => true,

    'websocket' => new Repository([
        'enabled' => false,
        'handler' => new Repository(null, Repository::DATA_AS_RAW | Repository::DATA_PROTECTED),
    ]),

    'queue' => new Repository([
        'key'          => 'queue:',
        'redis'        => 'default',
        'retrySeconds' => 5,
        'maxAttempts'  => 5,
    ]),

    'tables' => new Repository([], Repository::DATA_PROTECTED),

    'options' => new Repository([
        'reactor_num'     => $cpuNum * 2,
        'worker_num'      => $cpuNum * 2,
        'task_worker_num' => $cpuNum * 2,
        'dispatch_mode'   => 1,
        'daemonize'       => 0,
        'http_parse_post' => true,
    ]),
];
