<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 16:50:40 +0800
 */

namespace Teddy\Workerman\Processes;

use Teddy\Interfaces\ProcessInterface;
use Teddy\Interfaces\WorkermanProcessInterface;
use Workerman\Worker;

class CustomProcess extends AbstractWorkermanProcess implements WorkermanProcessInterface
{
    /**
     * @var ProcessInterface
     */
    protected $process;

    public function __construct(ProcessInterface $process)
    {
        $this->process = $process;
        $this->options = [
            'count' => $process->getCount(),
        ];
    }

    public function getName(): string
    {
        return $this->process->getName();
    }

    public function onWorkerStart(Worker $worker): void
    {
        $this->process->setWorker($worker);
        $this->process->handle();
    }

    public function onWorkerReload(Worker $worker): void
    {
        $this->process->onReload();
    }
}
