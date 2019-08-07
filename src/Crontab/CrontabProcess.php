<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 17:26:55 +0800
 */
namespace Teddy\Crontab;

use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Timer;
use Teddy\Interfaces\ProcessInterface;

class CrontabProcess implements ProcessInterface
{
    protected $crontabList = [];

    public function __construct(array $list)
    {
        $this->crontabList = $list;
    }

    public function getName(): string
    {
        return 'crontab';
    }

    public function handle(Server $swoole, Process $process)
    {
        Timer::tick(1000, function () {
            $time = time();
            $second = intval(date('s', $time));
            foreach ($this->crontabList as $item) {
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
        $process->exit(0);
    }
}
