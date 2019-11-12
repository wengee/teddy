<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-12 10:54:26 +0800
 */

if (!class_exists('GuzzleHttp\Client') || !class_exists('Swoole\Runtime')) {
    return;
}

// 替换默认 Handler
$class = 'GuzzleHttp\Client';
$ver   = $class::VERSION;
$ver   = explode('.', $ver);
array_pop($ver);
$ver = implode('.', $ver);
$dir = "/guzzle-{$ver}";

require __DIR__ . '/functions.php';
if (is_dir(__DIR__ . "/../vendor/mix/guzzle-hook/src/{$dir}")) {
    require __DIR__ . "/../vendor/mix/guzzle-hook/src/{$dir}/functions.php";
} elseif (is_dir(__DIR__ . "/../../../mix/guzzle-hook/src/{$dir}")) {
    require __DIR__ . "/../../../mix/guzzle-hook/src/{$dir}/functions.php";
}
