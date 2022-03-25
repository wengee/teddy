<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-21 16:40:13 +0800
 */

namespace Teddy\Swoole\Processes;

use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Timer;
use Teddy\Abstracts\AbstractProcess;
use Teddy\Crontab\Parser;
use Teddy\Interfaces\ProcessInterface;

class CrontabProcess extends AbstractProcess implements ProcessInterface
{
    protected $name = 'crontab process';

    protected $timerId;

    protected $list = [];

    public function __construct(array $list)
    {
        $this->list    = $list;
        $this->options = ['coroutine' => true];
    }

    public function handle(Server $swoole, Process $process): void
    {
        $this->timerId = Timer::tick(1000, function (): void {
            $timestamp = time();
            $second = (int) date('s', $timestamp);
            $minutes = (int) date('i', $timestamp);
            $hours = (int) date('G', $timestamp);
            $day = (int) date('j', $timestamp);
            $month = (int) date('n', $timestamp);
            $week = (int) date('w', $timestamp);

            foreach ($this->list as $item) {
                $timeCfg = $item[0] ?? null;
                $taskCls = $item[1] ?? null;
                $taskArgs = $item[2] ?? [];
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

                run_task($taskCls, $taskArgs);
            }
        });
    }

    public function onReload(Server $swoole, Process $process): void
    {
        if (null !== $this->timerId) {
            Timer::clear($this->timerId);
        }

        $process->exit(0);
    }
}
