<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:09:48 +0800
 */

namespace Teddy\Log;

use Illuminate\Support\Arr;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Psr\Log\LoggerInterface;
use Stringable;

class LogManager implements LoggerInterface
{
    protected string $appName;

    protected string $defaultChannel = 'default';

    protected int $defaultLevel = Logger::DEBUG;

    protected array $handlers = [];

    protected array $channels = [];

    protected string $dateFormat = 'Y-m-d H:i:s';

    public function __construct()
    {
        $this->appName = config('app.name') ?: 'Teddy App';

        $config = config('logger');
        if ($config) {
            $this->defaultChannel = $config['default'] ?? 'default';
            $this->defaultLevel   = $config['level'] ?? Logger::DEBUG;
            $this->handlers       = $config['handlers'] ?? [];
            $this->dateFormat     = $config['dateFormat'] ?? 'Y-m-d H:i:s';
        }
    }

    public function channel(string $channel): ?Logger
    {
        return $this->resolve($channel);
    }

    public function getDefaultChannel(): Logger
    {
        $channel = $this->channels[$this->defaultChannel] ?? null;
        if (!$channel) {
            $channel = $this->resolve($this->defaultChannel, false);

            $this->channels[$this->defaultChannel] = $channel;
        }

        return $channel;
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->getDefaultChannel()->emergency($message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->getDefaultChannel()->alert($message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->getDefaultChannel()->critical($message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->getDefaultChannel()->error($message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->getDefaultChannel()->warning($message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->getDefaultChannel()->notice($message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->getDefaultChannel()->info($message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->getDefaultChannel()->debug($message, $context);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->getDefaultChannel()->log($level, $message, $context);
    }

    protected function resolve(string $channel, bool $nullable = true): ?Logger
    {
        if (isset($this->channels[$channel])) {
            return $this->channels[$channel];
        }

        $config = $this->handlers[$channel] ?? null;
        if (!$config) {
            if (!$nullable) {
                return new Logger();
            }

            return null;
        }

        if ($config instanceof HandlerInterface) {
            return new Logger($config);
        }

        $config = Arr::wrap($config);
        $driver = $config['driver'] ?? 'null';
        $method = 'create'.ucfirst($driver).'Driver';
        if (method_exists($this, $method)) {
            $instance = $this->{$method}($config);
        } else {
            $instance = $this->createDriver($driver, $config);
        }

        $this->channels[$channel] = $instance;

        return $instance;
    }

    protected function createStackDriver(array $config): Logger
    {
        $handlers    = $config['handlers'] ?? [];
        $handlerObjs = [];
        foreach ($handlers as $handler) {
            $config = $this->handlers[$handler] ?? null;
            if ($config && isset($config['driver'])) {
                $method = 'create'.ucfirst($config['driver']).'Handler';
                if (method_exists($this, $method)) {
                    $handlerObjs[] = $this->{$method}($config);
                }
            }
        }

        return new Logger($handlerObjs);
    }

    protected function createDriver(string $driver, array $config = []): Logger
    {
        $handler = null;
        $method  = 'create'.ucfirst($driver).'Handler';
        if (method_exists($this, $method)) {
            $handler = $this->{$method}($config);
        }

        return new Logger($handler);
    }

    protected function createNullHandler(array $config = []): HandlerInterface
    {
        return $this->prepareHandler(new NullHandler(), $config);
    }

    protected function createFileHandler(array $config = []): HandlerInterface
    {
        return $this->prepareHandler(new StreamHandler(
            $config['path'] ?? 'php://stderr',
            $config['level'] ?? $this->defaultLevel,
            $config['bubble'] ?? true,
            $config['filePermission'] ?? null,
            $config['useLocking'] ?? false
        ), $config);
    }

    protected function createStreamHandler(array $config = []): HandlerInterface
    {
        return $this->createFileHandler($config);
    }

    protected function createDailyHandler(array $config = []): HandlerInterface
    {
        return $this->prepareHandler(new RotatingFileHandler(
            $config['path'],
            $config['days'] ?? 7,
            $config['level'] ?? $this->defaultLevel,
            $config['bubble'] ?? true,
            $config['filePermission'] ?? null,
            $config['useLocking'] ?? false
        ), $config);
    }

    protected function createSysLogHandler(array $config = []): HandlerInterface
    {
        return $this->prepareHandler(new SyslogHandler(
            $config['ident'] ?? $this->appName,
            $config['facility'] ?? LOG_USER,
            $config['level'] ?? $this->defaultLevel,
            $config['bubble'] ?? true
        ), $config);
    }

    protected function createErrorLogHandler(array $config = []): HandlerInterface
    {
        return $this->prepareHandler(new ErrorLogHandler(
            $config['messageType'] ?? ErrorLogHandler::OPERATING_SYSTEM,
            $config['level'] ?? $this->defaultLevel,
            $config['bubble'] ?? true,
            $config['expandNewlines'] ?? false
        ), $config);
    }

    protected function prepareHandler(HandlerInterface $handler, array $config = []): HandlerInterface
    {
        if ($handler instanceof AbstractProcessingHandler) {
            $formatter = $config['formatter'] ?? null;
            $handler->setFormatter($this->createFormatter($formatter));
        }

        return $handler;
    }

    protected function createFormatter($formatter = null)
    {
        if ($formatter instanceof FormatterInterface) {
            return $formatter;
        }
        if ('json' === $formatter) {
            return new JsonFormatter();
        }
        if ('html' === $formatter) {
            return new HtmlFormatter();
        }

        return new LineFormatter(null, $this->dateFormat, true, true);
    }
}
