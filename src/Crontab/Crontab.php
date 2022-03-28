<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-28 17:32:17 +0800
 */

namespace Teddy\Crontab;

use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Traits\ContainerAwareTrait;

class Crontab implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $list = [];

    public function __construct()
    {
        $this->list = config('crontab', []);
    }

    public function add(string $time, string $taskClass, array $args = []): void
    {
        $this->list[] = [$time, $taskClass, $args];
    }

    public function run(): void
    {
        if (!$this->list) {
            return;
        }

        $timestamp = time();
        $second    = (int) date('s', $timestamp);
        $minutes   = (int) date('i', $timestamp);
        $hours     = (int) date('G', $timestamp);
        $day       = (int) date('j', $timestamp);
        $month     = (int) date('n', $timestamp);
        $week      = (int) date('w', $timestamp);

        foreach ($this->list as $item) {
            $timeCfg  = $item[0] ?? null;
            $taskCls  = $item[1] ?? null;
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
    }
}
