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
     * @var integer
     */
    protected $delay = 0;

    public function delay(int $delay): Task
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
        safe_call([$this, 'handle']);
    }

    public static function deliver(Task $task)
    {
        $deliver = function () use ($task) {
            $swoole = app('swoole');
            if ($swoole) {
                return $swoole->task($task);
            }
        };

        if (defined('IN_SWOOLE') && IN_SWOOLE && $task->getDelay() > 0) {
            swoole_timer_after($task->getDelay() * 1000, $deliver);
            return true;
        } else {
            return $deliver();
        }
    }
}
