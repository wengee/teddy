<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:24:33 +0800
 */

require __DIR__ . '/../vendor/autoload.php';

Teddy\PharBuilder::build(__DIR__, [
    'main'      => 'server.php',

    'directories' => [
        'app',
        'bootstrap',
        'config',
        'resources',
        'routes',
        'templates',
        'vendor',
        'vivo'
    ],

    'files' => [
        'server.php',
    ],

    'copy' => [
        'config' => 'cfg',
    ],

    'exclude' => [
        'vendor/aliyuncs/oss-sdk-php/samples',
        'vendor/aliyuncs/oss-sdk-php/tests',
        'vendor/bacon/bacon-qr-code/tests',
        'vendor/container-interop/container-interop/docs',
        'vendor/doctrine/annotations/docs',
        'vendor/funkjedi/composer-include-files',
        'vendor/monolog/monolog/doc',
        'vendor/monolog/monolog/tests',
        'vendor/nikic/fast-route/test',
        'vendor/phpoption/phpoption/tests',
        'vendor/pimple/pimple/ext',
        'vendor/ralouphie/getallheaders/tests',
        'vendor/simplesoftwareio/simple-qrcode/docs',
        'vendor/simplesoftwareio/simple-qrcode/tests',
        'vendor/tuupola/http-factory/tests',
    ],
]);
