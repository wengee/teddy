<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-12 15:56:52 +0800
 */
namespace Teddy\Schedule;

use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Timer;
use Teddy\Interfaces\ProcessInterface;

class ScheduleProcess implements ProcessInterface
{
    protected $timerId = null;

    protected $scheduleList = [];

    public function __construct(array $list)
    {
        $this->scheduleList = $list;
    }

    public function getName(): string
    {
        return 'schedule';
    }

    public function enableCoroutine(): bool
    {
        return true;
    }

    public function handle(Server $swoole, Process $process)
    {
        $this->timerId = Timer::tick(1000, function () {
            $timestamp = time();
            $second = (int) date('s', $timestamp);
            $minutes = (int) date('i', $timestamp);
            $hours = (int) date('G', $timestamp);
            $day = (int) date('j', $timestamp);
            $month = (int) date('n', $timestamp);
            $week = (int) date('w', $timestamp);

            foreach ($this->scheduleList as $item) {
                $timeCfg = isset($item[0]) ? $item[0] : null;
                $taskCls = isset($item[1]) ? $item[1] : null;
                $taskArgs = isset($item[2]) ? $item[2] : [];
                if (!$timeCfg || !$taskCls) {
                    continue;
                }

                $seconds = Parser::check($timeCfg, $minutes, $hours, $day, $month, $week);
                if (!$seconds || !isset($seconds[$second])) {
                    continue;
                }

                if (!is_array($taskArgs)) {
                    $taskArgs = [$taskArgs];
                }

                (new $taskCls(...$taskArgs))->send();
            }
        });
    }

    public function onReload(Server $swoole, Process $process)
    {
        if ($this->timerId !== null) {
            Timer::clear($this->timerId);
        }

        $process->exit(0);
    }
}
