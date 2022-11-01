<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-01 14:20:44 +0800
 */

namespace App\Commands;

use Teddy\Abstracts\AbstractCommand;

class ConfigCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('config')
            ->setDescription('Print the config')
        ;
    }

    protected function handle(): void
    {
        print_r(json_encode(config('test'), JSON_PRETTY_PRINT));
    }
}
