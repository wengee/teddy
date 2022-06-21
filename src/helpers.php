<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-06-21 10:15:08 +0800
 */

use Exception;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Teddy\Config\Config;
use Teddy\Container\Container;
use Teddy\Hook;
use Teddy\Interfaces\ServerInterface;
use Teddy\Utils\FileSystem;
use Teddy\Validation\Field;
use Teddy\Validation\Validation;

defined('INT_STR_INDEX') || define('INT_STR_INDEX', 'l2Vj5aUOBCLpdFRsK6ytAXzGbY1QWewvHhxE4gT38SPqmfioc7Ju9NDr0IZMkn');

if (!function_exists('add_hook')) {
    /**
     * Add a hook.
     */
    function add_hook(string $name, callable $func): void
    {
        Hook::add($name, $func);
    }
}

if (!function_exists('run_hook')) {
    /**
     * Run hooks.
     */
    function run_hook(string $name, ?array $params): void
    {
        Hook::run($name, $params);
    }
}

if (!function_exists('make')) {
    /**
     * Make the instance.
     *
     * @param mixed $concrete
     */
    function make($concrete, ?array $arguments = null)
    {
        if (is_callable($concrete)) {
            return call_user_func_array($concrete, $arguments ?: []);
        }

        if (is_string($concrete) && class_exists($concrete)) {
            $reflection = new ReflectionClass($concrete);

            return $reflection->newInstanceArgs($arguments ?: []);
        }

        return null;
    }
}

if (!function_exists('response')) {
    /**
     * Make a response.
     */
    function response(): ResponseInterface
    {
        return Container::getInstance()->getNew('response');
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

if (!function_exists('run_task')) {
    /**
     * Run a task.
     *
     * @param null|array|bool|int $extra ['local' => true, 'at' => 0, 'delay' => 0]
     */
    function run_task(string $className, array $args = [], $extra = null): void
    {
        static $server;
        if (null === $server) {
            $server = Container::getInstance()->get(ServerInterface::class);
        }

        /** @var null|ServerInterface $server */
        if ($server) {
            $server->addTask($className, $args, $extra);
        } else {
            throw new Exception('Unable to run the task ['.$className.'].');
        }
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
            return $config->all();
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

if (!function_exists('validate')) {
    /**
     * Validate data with some rules.
     *
     * @param string|Validation $validation
     * @param Field[]           $fields
     */
    function validate($validation, array $data, array $fields = [], bool $safe = false)
    {
        static $instances = [];
        if ($validation instanceof Validation) {
            return $validation->validate($data, $fields, $safe);
        }

        $validation = strval($validation);
        if (!isset($instances[$validation])) {
            if (false !== strpos($validation, '.')) {
                $className = str_replace(' ', '\\', ucwords(str_replace('.', ' ', $validation)));
            } else {
                $className = ucfirst($validation);
            }

            $className = '\\App\\Validations\\'.$className;
            if (!class_exists($className)) {
                throw new RuntimeException('Validation ['.$className.'] is not defined.');
            }

            $instances[$validation] = new $className();
        }

        $instance = $instances[$validation];

        return $instance->validate($data, $fields, $safe);
    }
}

if (!function_exists('int2str')) {
    /**
     * Encode integer to string.
     */
    function int2str(int $num, string|int $base = 62): string
    {
        if (is_string($base)) {
            $index = $base;
            $base  = strlen($index);
        } else {
            $base  = (int) $base;
            $index = substr(INT_STR_INDEX, 0, $base);
        }

        $out = '';
        for ($t = floor(log10($num) / log10($base)); $t >= 0; --$t) {
            $a   = intval(floor($num / $base ** $t));
            $out = $out.substr($index, $a, 1);
            $num = $num - ($a * $base ** $t);
        }

        return $out;
    }
}

if (!function_exists('str2int')) {
    /**
     * Decode string to integer.
     */
    function str2int(string $num, string|int $base = 62): int
    {
        if (is_string($base)) {
            $index = $base;
            $base  = strlen($index);
        } else {
            $base  = (int) $base;
            $index = substr(INT_STR_INDEX, 0, $base);
        }

        $out = 0;
        $len = strlen($num) - 1;
        for ($t = 0; $t <= $len; ++$t) {
            $out = $out + strpos($index, substr($num, $t, 1)) * $base ** ($len - $t);
        }

        return (int) $out;
    }
}

if (!function_exists('base64_urlencode')) {
    /**
     * Encodes data with URL safe base64 string.
     */
    function base64_urlencode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64_urldecode')) {
    /**
     * Decodes data encoded with URL safe base64 string.
     */
    function base64_urldecode(string $data): string|false
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (!function_exists('cpu_count')) {
    /**
     * Get number of cpu logical processors.
     */
    function cpu_count(): int
    {
        static $count;
        if (!$count) {
            $os = strtolower(PHP_OS_FAMILY);

            switch ($os) {
                case 'windows':
                    $count = (int) shell_exec('echo %NUMBER_OF_PROCESSORS%');

                break;

                case 'linux':
                    $count = (int) shell_exec('nproc');

                break;

                case 'darwin':
                case 'bsd':
                case 'solaris':
                    $count = (int) shell_exec('grep -c ^processor /proc/cpuinfo');

                break;

                default:
                    $count = 1;
            }

            $count = ($count > 0) ? $count : 1;
        }

        return $count;
    }
}

if (!function_exists('swoole_defer')) {
    /**
     * Defers the execution of a callback function until the surrounding function of a coroutine returns. (for swoole).
     */
    function swoole_defer(callable $callback): void
    {
        if (defined('IN_SWOOLE') && IN_SWOOLE) {
            defer($callback);
        }
    }
}

if (!function_exists('in_swoole')) {
    function in_swoole(): bool
    {
        return defined('IN_SWOOLE') && IN_SWOOLE;
    }
}

if (!function_exists('in_workerman')) {
    function in_workerman(): bool
    {
        return defined('IN_WORKERMAN') && IN_WORKERMAN;
    }
}
