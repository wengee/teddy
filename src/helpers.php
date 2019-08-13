<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-13 17:46:33 +0800
 */

use Illuminate\Support\Str;
use Teddy\Container;

if (!function_exists('make')) {
    /**
     * Make the instance.
     *
     * @param mixed $abstract
     * @param array|null $parameters
     * @return mixed
     */
    function make($abstract, ?array $parameters = null)
    {
        return Container::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $make
     * @return mixed
     */
    function app(?string $make = null)
    {
        if ($make === null) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make);
    }
}

if (!function_exists('db')) {
    /**
     * Get a database connection.
     *
     * @param  string  $connection
     * @return Teddy\Database\Database|null
     */
    function db(string $connection = 'default')
    {
        $db = app('db');
        if (!$db) {
            return null;
        }

        return $db->connection($connection);
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the app path.
     *
     * @param string|null $path
     * @return string
     */
    function base_path(?string $path = null): string
    {
        static $basePath;
        if ($basePath === null) {
            $basePath = app()->getBasePath();
        }

        if (!$path) {
            return $basePath;
        }

        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('runtime_path')) {
    /**
     * Get the runtime_path path.
     *
     * @param string|null $path
     * @return string
     */
    function runtime_path(?string $path = null): string
    {
        static $runtimePath;
        if ($runtimePath === null) {
            $runtimePath = app()->getRuntimePath();
        }

        if (!$path) {
            return $runtimePath;
        }

        return rtrim($runtimePath, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('config')) {
    /**
     * Get the specified configuration value.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if ($key === null) {
            return app('config');
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('event')) {
    /**
     * @param string|League\Event\EventInterface $event
     * @param mixed $args
     * @return mixed
     */
    function event($event, ...$args)
    {
        return app('events')->emit($event, ...$args);
    }
}

if (!function_exists('log_message')) {
    /**
     * Write the message to logger.
     *
     * @param string|int $level
     * @param string $message
     * @param array $data
     * @return bool
     */
    function log_message($level, string $message, array $data = [])
    {
        $logger = app('logger');
        if (!$logger) {
            return false;
        }

        return $logger->log($level, sprintf($message, ...$data));
    }
}

if (!function_exists('log_exception')) {
    /**
     * Write the exception to logger.
     *
     * @param  Exception  $e
     * @return bool
     */
    function log_exception(Exception $e, string $prefix = '')
    {
        $logger = app('logger');
        if (!$logger) {
            return false;
        }

        $logger->error(sprintf(
            '%sUncaught exception "%s": [%d]%s called in %s:%d%s%s',
            $prefix,
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

if (!function_exists('safe_call')) {
    /**
     * Call a function safely, without exceptions.
     *
     * @param callable $func
     * @param array $args
     * @return mixed
     */
    function safe_call(callable $func, array $args = [])
    {
        try {
            $ret = $func(...$args);
        } catch (Exception $e) {
            log_exception($e, "Call a function throw exceptions.\n");
            return false;
        }

        return $ret ?: true;
    }
}

if (!function_exists('base64url_encode')) {
    /**
     * Encode the string to URL-safe base64.
     *
     * @param  string $data
     * @return string
     */
    function base64url_encode(string $data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    /**
     * Decode the URL-safe base64 string.
     *
     * @param  string $data
     * @return string
     */
    function base64url_decode(string $data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
