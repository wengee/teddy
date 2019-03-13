<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-02-21 16:28:48 +0800
 */
namespace Teddy;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;

abstract class Task
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var integer
     */
    protected $delay = 0;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    public function delay(int $delay)
    {
        if ($delay <= 0) {
            throw new InvalidArgumentException('The delay must be greater than 0');
        }

        $this->delay = $delay;
        return $this;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function send(?int $delay = null)
    {
        if ($delay !== null) {
            $this->delay($delay);
        }

        return static::deliver($this);
    }

    abstract public function handle();

    final public function safeRun()
    {
        try {
            $this->handle();
        } catch (\Exception $e) {
            $logger = $this->container->get('logger');
            if ($logger) {
                $logger->error(sprintf(
                    'Uncaught exception "%s": [%d]%s called in %s:%d%s%s',
                    get_class($e),
                    $e->getCode(),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    PHP_EOL,
                    $e->getTraceAsString()
                ));
            }

            return false;
        }

        return true;
    }

    public static function deliver(Task $task)
    {
        $deliver = function () use ($task) {
            $swoole = app('swoole');
            if (empty($swoole)) {
                \register_shutdown_function(function () use ($task) {
                    \fastcgi_finish_request();
                    $task->setContainer(app()->getContainer())
                         ->safeRun();
                });
                return true;
            } else {
                return $swoole->task($task);
            }
        };

        if (defined('IN_SWOOLE') && IN_SWOOLE && $task->getDelay() > 0) {
            \swoole_timer_after($task->getDelay() * 1000, $deliver);
            return true;
        } else {
            return $deliver();
        }
    }

    /**
     * Bridge container get.
     *
     * @param string $name
     */
    final public function __get($name)
    {
        return $this->container->get($name);
    }

    /**
     * Bridge container has.
     *
     * @param string $name
     */
    final public function __isset($name)
    {
        return $this->container->has($name);
    }
}
