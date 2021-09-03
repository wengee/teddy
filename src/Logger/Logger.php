<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Logger;

use Illuminate\Support\Arr;
use Monolog\Logger as MonoLogger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;

class Logger extends MonoLogger
{
    public function __construct($handlers = [], $processors = null)
    {
        $handlers = Arr::wrap($handlers);
        $appName  = config('app.name') ?: 'Teddy App';

        if (null === $processors) {
            $processors = [
                new PsrLogMessageProcessor(),
                new MemoryUsageProcessor(),
                new MemoryPeakUsageProcessor(),
            ];
        } else {
            $processors = Arr::wrap($processors);
        }

        parent::__construct($appName, $handlers, $processors);
    }
}
