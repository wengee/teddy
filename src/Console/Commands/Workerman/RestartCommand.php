<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:28:45 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class RestartCommand extends DefaultCommand
{
    protected $signature = 'workerman:restart
        {--d|daemon : Start in DAEMON mode}
        {--g|gracefully : Stop gracefully}';

    protected $description = 'Restart workerman workers';

    protected $action = 'restart';
}
