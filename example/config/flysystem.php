<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-02 10:27:59 +0800
 */

use Teddy\Config\Repository;

return [
    'default' => 'local',

    'disks' => [
        'local' => new Repository([
            'driver'        => 'local',
            'location'      => '/data/htdocs/uploaded',
            'url'           => 'https://lvh.me/uploaded',
            'visibility'    => 'public',
        ]),

        'oss' => new Repository([
            'driver'            => 'oss',
            'accessKeyId'       => '',
            'accessKeySecret'   => '',
            'securityToken'     => '',
            'bucket'            => '',
            'endpoint'          => '',
            'cdnDomain'         => '',
            'ssl'               => true,
            'isCName'           => true,
            'prefix'            => '',
            'timeout'           => 600,
            'connectTimeout'    => 10,
        ]),

        'cos' => new Repository([
            'driver'        => 'cos',
            'appId'         => '',
            'secretId'      => '',
            'secretKey'     => '',
            'region'        => '',
            'bucket'        => '',
            'signedUrl'     => false,
            'cdn'           => '',
            'prefix'        => '',
        ]),
    ],
];
