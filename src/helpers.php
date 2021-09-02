<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-30 17:09:26 +0800
 */

use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Support\Str;
use PhpOption\Option;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Teddy\Config\Config;
use Teddy\Container;
use Teddy\Utils\FileSystem;

if (!function_exists('make')) {
    /**
     * Make the instance.
     */
    function make(string $id, ?array $parameters = null)
    {
        return Container::getInstance()->make($id, $parameters);
    }
}

if (!function_exists('response')) {
    /**
     * Make a response.
     */
    function response(int $status = StatusCodeInterface::STATUS_OK): ResponseInterface
    {
        return make('response', [$status]);
    }
}

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     */
    function app(?string $id = null)
    {
        return Container::getInstance()->get($id ?: 'app');
    }
}

if (!function_exists('db')) {
    /**
     * Get a database connection.
     *
     * @return null|Teddy\Database\Database
     */
    function db(string $connection = 'default')
    {
        $db = Container::getInstance()->get('db');
        if (!$db) {
            return null;
        }

        return $db->connection($connection);
    }
}

if (!function_exists('path_join')) {
    /**
     * Join the paths.
     *
     * @param string ...$paths
     */
    function path_join(string $basePath, string ...$args): string
    {
        return FileSystem::joinPath($basePath, ...$args);
    }
}

if (!function_exists('system_path')) {
    /**
     * Get the system path.
     */
    function system_path(string ...$args): string
    {
        return FileSystem::joinPath(__DIR__, ...$args);
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the app path.
     */
    function base_path(string ...$args): string
    {
        static $basePath;
        if (null === $basePath) {
            $basePath = app('basePath');
        }

        return FileSystem::joinPath($basePath, ...$args);
    }
}

if (!function_exists('runtime_path')) {
    /**
     * Get the runtime path.
     */
    function runtime_path(string ...$args): string
    {
        static $runtimePath;
        if (null === $runtimePath) {
            $runtimePath = FileSystem::getRuntimePath() ?: app('basePath');
        }

        return FileSystem::joinPath($runtimePath, ...$args);
    }
}

if (!function_exists('vendor_path')) {
    /**
     * Get the vendor path.
     */
    function vendor_path(string ...$args): string
    {
        if (!class_exists('\\Composer\\Autoload\\ClassLoader')) {
            return '';
        }

        static $vendorPath;
        if (null === $vendorPath) {
            $refCls   = new ReflectionClass('\\Composer\\Autoload\\ClassLoader');
            $fileName = $refCls->getFileName();
            if ($fileName) {
                $vendorPath = dirname($fileName, 2);
            } else {
                $vendorPath = '';
            }
        }

        return FileSystem::joinPath($vendorPath, ...$args);
    }
}

if (!function_exists('config')) {
    /**
     * Get the specified configuration value.
     *
     * @param null|mixed $default
     */
    function config(?string $key = null, $default = null)
    {
        /** @var Config */
        $config = Container::getInstance()->get('config');

        if (null === $key) {
            return $config;
        }

        return $config->get($key, $default);
    }
}

if (!function_exists('log_message')) {
    /**
     * Write the message to logger.
     *
     * @param int|string $level
     * @param array      $data
     */
    function log_message($level, string $message, ...$data): void
    {
        /** @var null|LoggerInterface */
        $logger = Container::getInstance()->get('logger');
        if ($logger) {
            $logger->log($level, $data ? sprintf($message, ...$data) : $message);
        }
    }
}

if (!function_exists('log_exception')) {
    /**
     * Write the exception to logger.
     */
    function log_exception(Exception $e, string $prefix = ''): void
    {
        /** @var null|LoggerInterface */
        $logger = Container::getInstance()->get('logger');
        if ($logger) {
            $logger->error(sprintf(
                '%sException "%s": [%d]%s called in %s:%d%s%s',
                $prefix ? ($prefix.' ') : '',
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

if (!function_exists('unparse_url')) {
    /**
     * Conversion back to string from a parsed url.
     */
    function unparse_url(array $parsedUrl): string
    {
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'].'://' : '';
        $host     = $parsedUrl['host'] ?? '';
        $port     = isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '';
        $user     = $parsedUrl['user'] ?? '';
        $pass     = isset($parsedUrl['pass']) ? ':'.$parsedUrl['pass'] : '';
        $pass     = ($user || $pass) ? "{$pass}@" : '';
        $path     = $parsedUrl['path'] ?? '';
        $query    = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '';

        return "{$scheme}{$user}{$pass}{$host}{$port}{$path}{$query}{$fragment}";
    }
}

if (!function_exists('build_url')) {
    /**
     * Make a url with query params.
     *
     * @param array|string $queryArgs
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

        $parsedUrl['query'] = empty($parsedUrl['query']) ? $queryArgs : $parsedUrl['query'].'&'.$queryArgs;

        return unparse_url($parsedUrl);
    }
}

if (!function_exists('site_url')) {
    /**
     * Make a site url.
     *
     * @param string       $url
     * @param array|string $queryArgs
     */
    function site_url(string $path, $queryArgs = []): string
    {
        static $baseUrl;
        if (!isset($baseUrl)) {
            $baseUrl = Str::finish(config('app.baseUrl', '/'), '/');
        }

        if (!preg_match('#https?://.+#i', $path)) {
            $path = $baseUrl.ltrim($path, '/');
        }

        return build_url($path, $queryArgs);
    }
}
