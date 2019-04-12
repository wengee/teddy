<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-12 14:41:15 +0800
 */
namespace Teddy\Swoole\Traits;

use Illuminate\Support\Str;
use Swoole\Process;
use Swoole\Runtime;
use Teddy\Swoole\Server;
use Teddy\Swoole\Timer\CronJobInterface;
use Teddy\Utils;

trait HasTimerProcess
{
    protected function addTimerProcess(Server $server, array $config, bool $enableCoroutine = false)
    {
        if (empty($config['enable']) || empty($config['jobs']) || !value($config['enable'])) {
            return;
        }

        if (isset($config['host']) && !$this->isCurrentHost($config['host'])) {
            return;
        }

        $startTimer = function (Process $process) use ($server, $config, $enableCoroutine) {
            if (!empty($config['pid_file'])) {
                file_put_contents($config['pid_file'], $process->pid);
            }

            $server->initApp();
            $server->setProcessTitle('timer process');

            $timerIds = [];
            $jobs = (array) $config['jobs'];
            foreach ($jobs as $jobClass) {
                if (is_array($jobClass)) {
                    $args = array_values($jobClass);
                    $jobClass = array_shift($args);
                    $job = new $jobClass(...$args);
                } else {
                    $job = new $jobClass;
                }

                if (!($job instanceof CronJobInterface)) {
                    continue;
                }

                if ($job->interval() <= 0) {
                    continue;
                }

                $runProcess = function () use ($job, $enableCoroutine) {
                    $runCallback = function () use ($job, $enableCoroutine) {
                        if ($enableCoroutine) {
                            Runtime::enableCoroutine(true);
                        }

                        Utils::callWithCatchException([$job, 'run']);
                    };

                    if ($enableCoroutine) {
                        go($runCallback);
                    } else {
                        $runCallback();
                    }
                };

                $timerId = swoole_timer_tick($job->interval(), $runProcess);
                $timerIds[] = $timerId;
                $job->setTimerId($timerId);
                if ($job->isImmediate()) {
                    swoole_timer_after(1, $runProcess);
                }
            }

            Process::signal(SIGUSR1, function ($sigNo) use ($config, $timerIds, $process) {
                foreach ($timerIds as $timerId) {
                    swoole_timer_clear($timerId);
                }

                swoole_timer_after($config['max_wait_time'] * 1000, function () use ($process) {
                    $process->exit(0);
                });
            });
        };

        $timerProcess = new Process($startTimer, false, 0);
        if ($server->getSwoole()->addProcess($timerProcess)) {
            return $timerProcess;
        }
    }

    protected function isCurrentHost($host)
    {
        $thisHost = gethostname();
        if (is_string($host)) {
            return Str::is($host, $thisHost);
        } elseif (is_array($host)) {
            foreach ($host as $h) {
                if (Str::is($h, $thisHost)) {
                    return true;
                }
            }
        }

        return false;
    }
}
