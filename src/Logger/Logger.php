<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-21 10:38:29 +0800
 */

namespace Teddy\Logger;

use Monolog\Logger as MonoLogger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;

class Logger extends MonoLogger
{
    public function __construct($handlers = [])
    {
        $handlers = array_wrap($handlers);
        $appName = config('app.name') ?: 'Teddy App';
        $processors = [
            new PsrLogMessageProcessor,
            new MemoryUsageProcessor,
            new MemoryPeakUsageProcessor,
        ];

        parent::__construct($appName, $handlers, $processors);
    }
}
