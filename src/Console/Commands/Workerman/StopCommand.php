<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-10 11:24:03 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class StopCommand extends DefaultCommand
{
    protected $signature = 'workerman:stop
        {--g|gracefully : Stop gracefully}';

    protected $description = 'Stop workerman server';

    protected $action = 'stop';
}
