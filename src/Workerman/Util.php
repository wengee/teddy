<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-19 22:26:48 +0800
 */

namespace Teddy\Workerman;

use Teddy\Interfaces\ProcessInterface;
use Workerman\Worker;

class Util
{
    public static function bindWorker(Worker $worker, ProcessInterface $process): void
    {
        $callbackMap = [
            'onWorkerStart',
            'onWorkerReload',
            'onConnect',
            'onMessage',
            'onClose',
            'onError',
            'onBufferFull',
            'onBufferDrain',
        ];

        foreach ($callbackMap as $name) {
            if (method_exists($process, $name)) {
                $worker->{$name} = [$process, $name];
            }
        }
    }

    public static function startWorker(ProcessInterface $process): void
    {
        $worker = new Worker($process->getListen(), $process->getContext());
        $process->setWorker($worker);

        $worker->name = $process->getName();

        $propertyMap = [
            'count',
            'user',
            'group',
            'reloadable',
            'reusePort',
            'transport',
            'protocol',
        ];

        $options = $process->getOptions();
        foreach ($propertyMap as $property) {
            if (isset($options[$property])) {
                $worker->{$property} = $options[$property];
            }
        }

        self::bindWorker($worker, $process);
    }

    public static function runAll(): void
    {
        Worker::runAll();
    }
}
