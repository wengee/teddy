<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-15 23:00:17 +0800
 */

use Teddy\Config\Repository;

return [
    'default' => 'local',

    'disks' => [
        'local' => new Repository([
            'driver'     => 'local',
            'location'   => '/data/htdocs/uploaded',
            'url'        => 'https://lvh.me/uploaded',
            'visibility' => 'public',
        ]),

        'oss' => new Repository([
            'driver'          => 'oss',
            'accessKeyId'     => '',
            'accessKeySecret' => '',
            'bucket'          => '',
            'endpoint'        => '',
            'cdnDomain'       => '',
            'ssl'             => true,
            'isCName'         => false,
            'prefix'          => '',
            'timeout'         => 600,
            'connectTimeout'  => 10,
        ]),

        'cos' => new Repository([
            'driver'      => 'cos',
            'region'      => '',
            'bucket'      => '',
            'credentials' => new Repository([
                'secretId'  => '',
                'secretKey' => '',
            ]),
            'cdnDomain' => '',
            'prefix'    => '',
        ]),
    ],
];
