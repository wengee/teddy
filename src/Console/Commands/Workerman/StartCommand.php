<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:26:18 +0800
 */

namespace Teddy\Console\Commands\Workerman;

use Symfony\Component\Console\Input\InputOption;

class StartCommand extends DefaultCommand
{
    protected string $action = 'start';

    protected function configure(): void
    {
        $this->setName('workerman:start')
            ->setDefinition([
                new InputOption('daemon', 'd', InputOption::VALUE_NONE, 'Start in DAEMON mode'),
            ])
            ->setDescription('Start workerman server')
        ;
    }
}
