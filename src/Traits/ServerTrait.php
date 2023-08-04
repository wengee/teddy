<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-08-04 17:28:12 +0800
 */

namespace Teddy\Traits;

trait ServerTrait
{
    protected function generateStats(array $extra = []): array
    {
        return [
            'uname'          => php_uname(),
            'hostname'       => gethostname(),
            'currentWorkPid' => getmypid(),
            'phpVersion'     => PHP_VERSION,
            'startTime'      => $this->startTime,
            'loadAverage'    => function_exists('sys_getloadavg') ? sys_getloadavg() : null,

            'memory' => [
                'usage'         => memory_get_usage(),
                'realUsage'     => memory_get_usage(true),
                'peakUsage'     => memory_get_peak_usage(),
                'peakRealUsage' => memory_get_peak_usage(true),
            ],

            ...$extra,
        ];
    }
}
