<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-10 11:08:46 +0800
 */

use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;
use PhpOption\Option;
use Teddy\Container;
use Teddy\Utils\FileSystem;

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
        return FileSystem::joinPath($basePath, ...$args);
    }
}

if (!function_exists('system_path')) {
    /**
     * Get the system path.
     *
     * @param string $args
     * @return string
     */
    function system_path(string ...$args): string
    {
        $args = array_filter(array_map(function ($arg) {
            return trim($arg, '/\\');
        }, $args), 'strlen');

        return __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $args);
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
            $logger->log($level, $data ? sprintf($message, ...$data) : $message);
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
                '%sException "%s": [%d]%s called in %s:%d%s%s',
                $prefix ? ($prefix . ' ') : '',
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

if (!function_exists('unparse_url')) {
    /**
     * Conversion back to string from a parsed url.
     *
     * @param  array $parsedUrl
     * @return string
     */
    function unparse_url(array $parsedUrl): string
    {
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host     = $parsedUrl['host'] ?? '';
        $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user     = $parsedUrl['user'] ?? '';
        $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = $parsedUrl['path'] ?? '';
        $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}

if (!function_exists('build_url')) {
    /**
     * Make a url with query params.
     *
     * @param  string $url
     * @param  string|array $queryArgs
     * @return string
     */
    function build_url(string $url, $queryArgs = []): string
    {
        if (!$queryArgs) {
            return $url;
        }

        if (is_array($queryArgs)) {
            $queryArgs = http_build_query($queryArgs);
        } else {
            $queryArgs = strval($queryArgs);
        }

        $parsedUrl = parse_url($url);
        $parsedUrl['query'] = empty($parsedUrl['query']) ? $queryArgs : $parsedUrl['query'] . '&' . $queryArgs;
        return unparse_url($parsedUrl);
    }
}

if (!function_exists('site_url')) {
    /**
     * Make a site url.
     *
     * @param  string $url
     * @param  string|array $queryArgs
     * @return string
     */
    function site_url(string $path, $queryArgs = []): string
    {
        static $baseUrl;
        if (!isset($baseUrl)) {
            $baseUrl = str_finish(config('app.baseUrl', '/'), '/');
        }

        if (!preg_match('#https?://.+#i', $path)) {
            $path = $baseUrl . ltrim($path, '/');
        }

        return build_url($path, $queryArgs);
    }
}
