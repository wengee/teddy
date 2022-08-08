<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:28:53 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class StopCommand extends DefaultCommand
{
    protected $signature = 'workerman:stop
        {--g|gracefully : Stop gracefully}';

    protected $description = 'Stop workerman server';

    protected $action = 'stop';
}
