<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-04 16:02:03 +0800
 */

namespace Teddy\Traits;

use Teddy\Interfaces\ProcessInterface;
use Teddy\Queue\QueueProcess;
use Teddy\Schedule\ScheduleProcess;

trait ProcessTrait
{
    /** @var ProcessInterface[] */
    protected $processes = [];

    public function addScheduleProcess(array $list): ProcessInterface
    {
        $process = new ScheduleProcess($list);

        return $this->addProcess($process);
    }

    public function addQueueProcess(array $options): ProcessInterface
    {
        $process = new QueueProcess($options);

        return $this->addProcess($process);
    }

    public function addProcess(ProcessInterface $process): ProcessInterface
    {
        $this->processes[] = $process;

        return $process;
    }

    /**
     * @return ProcessInterface[]
     */
    public function listProcesses(): array
    {
        return $this->processes;
    }
}
