<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 14:43:40 +0800
 */

namespace Teddy\Console\Commands\Workerman;

use Symfony\Component\Console\Input\InputOption;

class StartCommand extends DefaultCommand
{
    protected $action = 'start';

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
