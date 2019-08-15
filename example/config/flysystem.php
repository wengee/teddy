<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:53 +0800
 */

return [
    'default' => env('FLYSYSTEM_DRIVER', 'local'),

    'disks' => [
        'local' => [
            'driver'     => 'local',
            'root'       => env('UPLOAD_PATH', '/data/htdocs/uploaded'),
            'url'        => env('UPLOAD_URL', 'http://lvh.me/uploaded'),
            'visibility' => 'public',
        ],

        'oss' => [
            'driver'     => 'oss',
            'access_id'  => env('OSS_ACCESS_ID', ''),
            'access_key' => env('OSS_ACCESS_KEY', ''),
            'bucket'     => env('OSS_BUCKET', ''),
            'endpoint'   => env('OSS_ENDPOINT', ''),
            'cdnDomain'  => env('OSS_CDN_DOMAIN', ''),
            'prefix'     => env('OSS_PREFIX', ''),
            'ssl'        => (bool) env('OSS_SSL', true),
            'isCName'    => (bool) env('OSS_IS_CNAME', true),
        ],
    ],
];
