<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-21 15:08:16 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class ConnectionsCommand extends DefaultCommand
{
    protected $signature = 'workerman:connections';

    protected $description = 'Show workerman connections';

    protected $action = 'connections';
}
