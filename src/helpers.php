<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 11:17:59 +0800
 */

use SlimExtra\App;
use SlimExtra\Db\RawSQL;

if (!function_exists('app')) {
    function app(?string $service = null)
    {
        $app = App::instance();
        if (empty($service)) {
            return $app;
        }

        if ($app->getContainer()->has($service)) {
            return $app->getContainer()->get($service);
        }

        return null;
    }
}

if (!function_exists('config')) {
    function config($key, $default = null)
    {
        static $settings;
        if (!isset($settings)) {
            $settings = app('settings');
        }

        return $settings->get($key, $default);
    }
}

if (!function_exists('raw_sql')) {
    function raw_sql(string $sql, ...$data)
    {
        return new RawSQL($sql, ...$data);
    }
}

if (!function_exists('fastcgi_finish_request')) {
    function fastcgi_finish_request()
    {
        return false;
    }
}
