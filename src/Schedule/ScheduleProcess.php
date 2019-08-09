<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-08 15:16:26 +0800
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

    public function handle(Server $swoole, Process $process)
    {
        $this->timerId = Timer::tick(1000, function () {
            $time = time();
            $second = intval(date('s', $time));
            foreach ($this->scheduleList as $item) {
                $timeCfg = isset($item[0]) ? $item[0] : null;
                $taskCls = isset($item[1]) ? $item[1] : null;
                $taskArgs = isset($item[2]) ? $item[2] : [];
                if (!$timeCfg || !$taskCls) {
                    continue;
                }

                $seconds = Parser::parse($timeCfg, $time);
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
