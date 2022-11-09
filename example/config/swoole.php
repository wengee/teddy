<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 16:34:24 +0800
 */

use Teddy\Config\Repository;

return [
    'options' => new Repository([
        'reactor_num'     => 2,
        'worker_num'      => 2,
        'task_worker_num' => 2,
        'dispatch_mode'   => 1,
        'daemonize'       => 0,
        'http_parse_post' => true,
    ]),
];
