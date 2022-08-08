<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:28:50 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class StatusCommand extends DefaultCommand
{
    protected $signature = 'workerman:status
        {--l|live : Show live status}';

    protected $description = 'Show workerman status';

    protected $action = 'status';
}
