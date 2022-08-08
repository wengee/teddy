<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:28:47 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class StartCommand extends DefaultCommand
{
    protected $signature = 'workerman:start
        {--d|daemon : Start in DAEMON mode}';

    protected $description = 'Start workerman server';

    protected $action = 'start';
}
