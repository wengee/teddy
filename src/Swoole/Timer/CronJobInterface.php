<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-10 16:14:34 +0800
 */
namespace Teddy\Swoole\Timer;

interface CronJobInterface
{
    /**
     * @return int $interval ms
     */
    public function interval(): int;

    /**
     * @return bool $isImmediate
     */
    public function isImmediate(): bool;

    /**
     * @var int $timerId
     */
    public function setTimerId(int $timerId);

    public function run();

    public function stop();
}
