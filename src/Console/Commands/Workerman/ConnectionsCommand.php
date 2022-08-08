<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:28:23 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class ConnectionsCommand extends DefaultCommand
{
    protected $signature = 'workerman:connections';

    protected $description = 'Show workerman connections';

    protected $action = 'connections';
}
