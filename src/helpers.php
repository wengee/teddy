<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-10-07 15:14:14 +0800
 */

use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;
use PhpOption\Option;
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

if (!function_exists('path_join')) {
    /**
     * Join the paths
     *
     * @param string ...$paths
     * @return string
     */
    function path_join(string $basePath, string ...$args)
    {
        $basePath = rtrim($basePath, '/\\');
        $args = array_map(function ($arg) {
            return trim($arg, '/\\');
        }, $args);

        return $basePath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $args);
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

        return rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }
}

if (!function_exists('runtime_path')) {
    /**
     * Get the runtime path.
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

        return rtrim($runtimePath, '/\\') . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }
}

if (!function_exists('vendor_path')) {
    /**
     * Get the vendor path.
     *
     * @param string|null $path
     * @return string
     */
    function vendor_path(?string $path = null): string
    {
        if (!class_exists('\\Composer\\Autoload\\ClassLoader')) {
            return '';
        }

        static $vendorPath;
        if ($vendorPath === null) {
            $refCls = new ReflectionClass('\\Composer\\Autoload\\ClassLoader');
            $fileName = $refCls->getFileName();
            if ($fileName) {
                $vendorPath = dirname($fileName, 2);
            } else {
                $vendorPath = '';
            }
        }

        if (!$path) {
            return $vendorPath;
        }

        return rtrim($vendorPath, '/\\') . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
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

if (!function_exists('env_get')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @param  string|array  $options
     * @return mixed
     */
    function env_get($key, $default = null, $options = null)
    {
        static $variables;

        if ($variables === null) {
            $variables = (new DotenvFactory([new EnvConstAdapter, new PutenvAdapter, new ServerConstAdapter]))->createImmutable();
        }

        return Option::fromValue($variables->get($key))
            ->map(function ($value) use ($options) {
                $filter = null;
                $separator = ',';

                if (is_string($options)) {
                    $filter = $options;
                } elseif (is_array($options)) {
                    if (isset($options['separator'])) {
                        $separator = $options['separator'];
                        $filter = 'list';
                    } else {
                        $filter = $options['filter'] ?? $filter;
                    }
                }

                if ($filter) {
                    switch ($filter) {
                        case 'int':
                        case 'integer':
                            return intval($value);

                        case 'float':
                        case 'double':
                            return floatval($value);

                        case 'json':
                            return json_decode($value, true);

                        case 'list':
                            return explode($separator, $value);
                    }
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

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            })
            ->getOrCall(function () use ($default) {
                return value($default);
            });
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
     * @return void
     */
    function log_message($level, string $message, ...$data): void
    {
        $logger = app('logger');
        if ($logger) {
            $logger->log($level, sprintf($message, ...$data));
        }
    }
}

if (!function_exists('log_exception')) {
    /**
     * Write the exception to logger.
     *
     * @param  Exception  $e
     * @return void
     */
    function log_exception(Exception $e, string $prefix = ''): void
    {
        $logger = app('logger');
        if ($logger) {
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
        }
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
    function base64url_encode(string $data): string
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
    function base64url_decode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (!function_exists('base64url_serialize')) {
    /**
     * Serialize a php variable to URL-safe base64.
     *
     * @param  mixed $data
     * @return string
     */
    function base64url_serialize($data): string
    {
        return base64url_encode(serialize($data));
    }
}

if (!function_exists('base64url_unserialize')) {
    /**
     * Unserialize the URL-safe base64 string.
     *
     * @param  string $data
     * @return mixed
     */
    function base64url_unserialize(string $data)
    {
        return unserialize(base64url_decode($data));
    }
}
