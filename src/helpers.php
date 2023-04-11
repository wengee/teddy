<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-11 14:07:06 +0800
 */

use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Teddy\Config\Config;
use Teddy\Container\Container;
use Teddy\Deferred;
use Teddy\Http\Response;
use Teddy\Interfaces\QueueInterface;
use Teddy\Log\LogManager;
use Teddy\Utils\FileSystem;
use Teddy\Validation\Field;
use Teddy\Validation\Validation;

define('INT_STR_DEFAULT_INDEX', 'l2Vj5aUOBCLpdFRsK6ytAXzGbY1QWewvHhxE4gT38SPqmfioc7Ju9NDr0IZMkn');

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
            $arguments = $arguments ?: [];

            return new $concrete(...$arguments);
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

if (!function_exists('json')) {
    /**
     * Make a JSON response.
     */
    function json(...$args): ResponseInterface
    {
        $response = response();
        if ($response instanceof Response) {
            return $response->json(...$args);
        }

        $data = ['errmsg' => null, 'errcode' => -1];
        foreach ($args as $arg) {
            if ($arg instanceof JsonSerializable) {
                $data = $arg;

                break;
            }

            if ($arg instanceof Exception) {
                $data['errcode'] = $arg->getCode() ?: -1;
                $data['errmsg']  = $arg->getMessage();
            } elseif (is_int($arg)) {
                $data['errcode'] = $arg;
            } elseif (is_string($arg)) {
                $data['errmsg'] = $arg;
            } elseif (is_array($arg)) {
                $data = array_merge($data, $arg);
            }
        }

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(StatusCodeInterface::STATUS_OK)
        ;
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
     * @return Teddy\Database\Database
     */
    function db(string $connection = 'default')
    {
        static $db;
        if (null === $db) {
            $db = Container::getInstance()->get('db');
        }

        if (!$db) {
            throw new Teddy\Exception('Database component is not found.');
        }

        return $db->connection($connection);
    }
}

if (!function_exists('redis')) {
    /**
     * Get a redis connection.
     *
     * @return Teddy\Redis\Redis
     */
    function redis(string $connection = 'default')
    {
        static $redis;
        if (null === $redis) {
            $redis = Container::getInstance()->get('redis');
        }

        if (!$redis) {
            throw new Teddy\Exception('Redis component is not found.');
        }

        return $redis->connection($connection);
    }
}

if (!function_exists('logger')) {
    /**
     * Get a logger.
     *
     * @return Teddy\Log\Logger
     */
    function logger(?string $channel = null)
    {
        static $logger;
        if (null === $logger) {
            /**
             * @var Teddy\Log\LogManager $logger
             */
            $logger = Container::getInstance()->get('logger');
        }

        if (null === $channel) {
            return $logger->getDefaultChannel();
        }

        return $logger->channel($channel);
    }
}

if (!function_exists('run_task')) {
    /**
     * Run a task.
     *
     * @param array $options Default: ['queue' => 'default', 'at' => 0, 'delay' => 0]
     */
    function run_task(string $className, array $args = [], array $options = []): void
    {
        static $queue;
        if (null === $queue) {
            $queue = Container::getInstance()->get(QueueInterface::class);
        }

        /**
         * @var null|QueueInterface $queue
         */
        if ($queue) {
            $queue->addTask($className, $args, $options);
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
        static $config;
        if (null === $config) {
            /**
             * @var Config
             */
            $config = Container::getInstance()->get('config');
        }

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
     * @param int|string        $level
     * @param string|Stringable $message
     * @param array             $data
     */
    function log_message(?string $channel, $level, string $message, ...$data): void
    {
        static $logManager;
        if (null === $logManager) {
            /**
             * @var LogManager
             */
            $logManager = Container::getInstance()->get('logger');
        }

        /**
         * @var LoggerInterface
         */
        $logger = $channel ? ($logManager->channel($channel) ?: $logManager) : $logManager;
        if ($logger) {
            $logger->log($level, $data ? sprintf($message, ...$data) : $message);
        }
    }
}

if (!function_exists('log_exception')) {
    /**
     * Write the exception to logger.
     */
    function log_exception(Throwable $e, string $prefix = ''): void
    {
        /**
         * @var null|LoggerInterface
         */
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
        static $intStrIndex;
        if (null === $intStrIndex) {
            $intStrIndex = env('INT_STR_INDEX', INT_STR_DEFAULT_INDEX);
        }

        if (is_string($base)) {
            $index = $base;
            $base  = strlen($index);
        } else {
            $base  = (int) $base;
            $index = substr($intStrIndex, 0, $base);
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
        static $intStrIndex;
        if (null === $intStrIndex) {
            $intStrIndex = env('INT_STR_INDEX', INT_STR_DEFAULT_INDEX);
        }

        if (is_string($base)) {
            $index = $base;
            $base  = strlen($index);
        } else {
            $base  = (int) $base;
            $index = substr($intStrIndex, 0, $base);
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

if (!function_exists('teddy_cpu_num')) {
    /**
     * Get number of cpu logical processors.
     */
    function teddy_cpu_num(): int
    {
        static $count;
        if (!$count) {
            $os = strtolower(PHP_OS_FAMILY);

            switch ($os) {
                case 'windows':
                    $count = (int) shell_exec('echo %NUMBER_OF_PROCESSORS%');

                break;

                case 'linux':
                    $count = (int) shell_exec('nproc --all');

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

if (!function_exists('teddy_defer')) {
    /**
     * Defers the execution of a callback function until the surrounding function of a coroutine returns. (for swoole).
     */
    function teddy_defer(?callable $callback = null): void
    {
        if (null === $callback) {
            Deferred::run();
        } else {
            Deferred::add($callback);
        }
    }
}
