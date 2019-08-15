<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:53 +0800
 */

return [
    'handlers' => [
        'daily' => [
            'path' => __DIR__ . '/../runtime/app.log',
        ],
        'file' => [
            'path' => __DIR__ . '/../runtime/teddy.log',
        ],
    ],
];
