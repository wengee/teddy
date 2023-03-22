<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:26:25 +0800
 */

namespace Teddy\Console\Commands\Workerman;

use Symfony\Component\Console\Input\InputOption;

class StopCommand extends DefaultCommand
{
    protected string $action = 'stop';

    protected function configure(): void
    {
        $this->setName('workerman:stop')
            ->setDefinition([
                new InputOption('gracefully', 'g', InputOption::VALUE_NONE, 'Stop gracefully'),
            ])
            ->setDescription('Stop workerman server')
        ;
    }
}
