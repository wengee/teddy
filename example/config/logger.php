<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-22 11:21:53 +0800
 */

use Teddy\Config\Repository;

return [
    'default' => 'default',

    'handlers' => new Repository([
        'default' => new Repository([
            'driver' => 'daily',
            'path'   => runtime_path('runtime/teddy.log'),
        ]),

        'console' => new Repository([
            'driver' => 'file',
            'path'   => 'php://stderr',
        ]),
    ]),
];
