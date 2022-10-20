<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 14:41:20 +0800
 */

namespace Teddy\Console\Commands\Workerman;

use Symfony\Component\Console\Input\InputOption;

class ReloadCommand extends DefaultCommand
{
    protected $action = 'reload';

    protected function configure(): void
    {
        $this->setName('workerman:reload')
            ->setDefinition([
                new InputOption('gracefully', 'g', InputOption::VALUE_NONE, 'Stop gracefully'),
            ])
            ->setDescription('Reload codes')
        ;
    }
}
