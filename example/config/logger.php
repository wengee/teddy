<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-02 14:25:18 +0800
 */

use Teddy\Config\Repository;

return [
    'default'   => 'test',

    'handlers'  => new Repository([
        'test'      => new Repository([
            'driver'    => 'stack',
            'handlers'  => ['file', 'console'],
        ]),

        'file'      => new Repository([
            'driver'    => 'file',
            'path'      => __DIR__.'/../runtime/teddy.log',
        ]),

        'console'   => new Repository([
            'driver'    => 'file',
            'path'      => 'php://stderr',
        ]),
    ]),
];
