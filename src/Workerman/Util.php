<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 20:38:40 +0800
 */

namespace Teddy\Workerman;

use Teddy\Workerman\ProcessInterface as WorkermanProcessInterface;
use Workerman\Worker;

class Util
{
    public static function bindWorker(Worker $worker, WorkermanProcessInterface $process): void
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

    public static function startWorker(WorkermanProcessInterface $process): void
    {
        $worker = new Worker($process->getListen(), $process->getContext());

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
