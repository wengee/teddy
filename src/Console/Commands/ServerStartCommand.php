<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-12 15:25:21 +0800
 */

namespace Teddy\Console\Commands;

use Teddy\Console\Command;

class ServerStartCommand extends Command
{
    protected $name = 'start';

    protected $description = 'Start teddy server';

    protected function handle(): void
    {
        $this->info('Starting web server...');
        app()->listen();
    }
}
