<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-21 17:55:15 +0800
 */

namespace App\Commands;

use Teddy\Console\Command;

class ConfigCommand extends Command
{
    protected $name = 'config';

    protected $description = 'Print the config';

    protected function handle(): void
    {
        var_dump(config('test'));
    }
}
