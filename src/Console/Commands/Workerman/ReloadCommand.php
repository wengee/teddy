<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:28:42 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class ReloadCommand extends DefaultCommand
{
    protected $signature = 'workerman:reload
        {--g|gracefully : Stop gracefully}';

    protected $description = 'Reload codes';

    protected $action = 'reload';
}
