<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-21 11:05:44 +0800
 */

return [
    'default'   => 'test',

    'handlers'  => [
        'test'  => [
            'driver'    => 'stack',
            'handlers'  => ['file', 'console'],
        ],

        'file'  => [
            'driver'    => 'file',
            'path'      => __DIR__ . '/../runtime/teddy.log',
        ],

        'console'   => [
            'driver'    => 'file',
            'path'      => 'php://stderr',
        ],
    ],
];
