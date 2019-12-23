<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-23 10:22:36 +0800
 */

namespace Teddy\Logger;

use Monolog\Logger as MonoLogger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;

class Logger extends MonoLogger
{
    public function __construct($handlers = [], $processors = null)
    {
        $handlers = array_wrap($handlers);
        $appName = config('app.name') ?: 'Teddy App';

        if ($processors === null) {
            $processors = [
                new PsrLogMessageProcessor,
                new MemoryUsageProcessor,
                new MemoryPeakUsageProcessor,
            ];
        } else {
            $processors = array_wrap($processors);
        }

        parent::__construct($appName, $handlers, $processors);
    }
}
