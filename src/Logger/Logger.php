<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger as LoggerBase;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;

class Logger extends LoggerBase
{
    public function __construct()
    {
        $appName = config('app.name') ?: 'Teddy App';
        $processors = [
            new PsrLogMessageProcessor,
            new MemoryUsageProcessor,
            new MemoryPeakUsageProcessor,
        ];

        $handlerObjs = [];
        $handlers = config('logger.handlers', []);
        $level = config('logger.level', 'DEBUG');
        foreach ($handlers as $key => $value) {
            if (is_subclass_of($value, HandlerInterface::class)) {
                $handlerObjs[] = is_object($value) ? $value : new $value;
            } elseif ($key === 'file' && is_string($value)) {
                $handlerObjs[] = new StreamHandler($value, $level);
            } elseif (is_string($key) && is_array($value)) {
                $method = 'create' . ucfirst($key) . 'Handler';
                if (method_exists($this, $method)) {
                    $value += [
                        'appName' => $appName,
                        'level' => $level,
                        'bubble' => true,
                    ];

                    $handler = $this->{$method}($value);
                    if ($handler) {
                        $handlerObjs[] = $handler;
                    }
                }
            }
        }

        parent::__construct($appName, $handlerObjs, $processors);
    }

    protected function createFileHandler(array $config)
    {
        if (!isset($config['path'])) {
            return null;
        }

        return $this->prepareHandler(new StreamHandler(
            $config['path'],
            $config['level'],
            $config['bubble'],
            $config['filePermission'] ?? null,
            $config['useLocking'] ?? false
        ), $config);
    }

    protected function createDailyHandler(array $config)
    {
        if (!isset($config['path'])) {
            return null;
        }

        return $this->prepareHandler(new RotatingFileHandler(
            $config['path'],
            $config['days'] ?? 7,
            $config['level'],
            $config['bubble'],
            $config['filePermission'] ?? null,
            $config['useLocking'] ?? false
        ), $config);
    }

    protected function createSyslogHandler(array $config)
    {
        return $this->prepareHandler(new SyslogHandler(
            $config['appName'],
            $config['facility'] ?? LOG_USER,
            $config['level'],
            $config['bubble']
        ), $config);
    }

    protected function prepareHandler(HandlerInterface $handler, array $config = [])
    {
        if (isset($config['formatter'])) {
            $handler->setFormatter($this->createFormatter($config['formatter']));
        }

        return $handler;
    }

    protected function createFormatter($formatter)
    {
        if ($formatter instanceof FormatterInterface) {
            return $formatter;
        } elseif ($formatter === 'json') {
            return new JsonFormatter;
        } elseif ($formatter === 'html') {
            return new HtmlFormatter;
        }

        return new LineFormatter;
    }
}
