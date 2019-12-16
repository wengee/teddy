<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-16 17:08:07 +0800
 */

return [
    'default' => env('FLYSYSTEM_DRIVER', 'local'),

    'disks' => [
        'local' => [
            'driver'        => 'local',
            'root'          => env('UPLOAD_PATH', '/data/htdocs/uploaded'),
            'url'           => env('UPLOAD_URL', 'http://lvh.me/uploaded'),
            'visibility'    => 'public',
        ],

        'oss' => [
            'driver'        => 'oss',
            'accessId'      => env('OSS_ACCESS_ID', ''),
            'accessKey'     => env('OSS_ACCESS_KEY', ''),
            'bucket'        => env('OSS_BUCKET', ''),
            'endpoint'      => env('OSS_ENDPOINT', ''),
            'cdnDomain'     => env('OSS_CDN_DOMAIN', ''),
            'prefix'        => env('OSS_PREFIX', ''),
            'ssl'           => (bool) env('OSS_SSL', true),
            'isCName'       => (bool) env('OSS_IS_CNAME', true),
        ],

        'cos' => [
            'driver'        => 'cos',
            'region'        => env('COS_REGION', ''),
            'appId'         => env('COS_APP_ID', ''),
            'secretId'      => env('COS_SECRET_ID', ''),
            'secretKey'     => env('COS_SECRET_KEY', ''),
            'bucket'        => env('COS_BUCKET', ''),
            'cdnDomain'     => env('COS_CDN_DOMAIN', ''),
            'scheme'        => env('COS_SCHEME', 'http'),
            'readFromCdn'   => (bool) env('COS_READ_FROM_CDN', false),
        ],
    ],
];
