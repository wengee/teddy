<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-10 17:54:24 +0800
 */
namespace Teddy\Swoole\Timer;

abstract class CronJob implements CronJobInterface
{
    /**
     * Swoole timer id
     * @var int
     */
    protected $timerId;

    /**
     * The interval of Job in millisecond
     * @var int
     */
    protected $interval = 0;

    /**
     * Whether run immediately after start
     * @var bool
     */
    protected $isImmediate = false;

    public function __construct(...$args)
    {
        if (isset($args[0])) {
            $this->interval = (int) $args[0];
        }

        if (isset($args[1])) {
            $this->isImmediate = (bool) $args[1];
        }
    }

    /**
     * @return int $interval ms
     */
    public function interval(): int
    {
        return $this->interval;
    }

    /**
     * @return bool $isImmediate
     */
    public function isImmediate(): bool
    {
        return $this->isImmediate;
    }

    abstract public function run();

    public function setTimerId(int $timerId)
    {
        $this->timerId = $timerId;
    }

    public function stop()
    {
        if (!empty($this->timerId)) {
            swoole_timer_clear($this->timerId);
        }
    }
}
