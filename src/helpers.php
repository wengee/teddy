<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-05-11 14:18:25 +0800
 */

use Teddy\Application;
use Teddy\Db\RawSQL;

if (!function_exists('app')) {
    function app(?string $service = null)
    {
        $app = Application::instance();
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

        return array_get($settings, $key, $default);
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

if (!function_exists('log_exception')) {
    function log_exception(Exception $e, $obj = null)
    {
        try {
            $logger = app('logger');
        } catch (Exception $err) {
            return false;
        }

        $logger->error(sprintf(
            '%sUncaught exception "%s": [%d]%s called in %s:%d%s%s',
            $obj ? '[' . get_class($obj) . '] ' : '',
            get_class($e),
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            PHP_EOL,
            $e->getTraceAsString()
        ));
        return true;
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
