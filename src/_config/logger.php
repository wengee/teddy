<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

use Monolog\Logger;
use Teddy\Config\Repository;

return [
    'default'    => '',
    'level'      => Logger::DEBUG,
    'dateFormat' => 'Y-m-d H:i:s',
    'handlers'   => new Repository([]),
];
