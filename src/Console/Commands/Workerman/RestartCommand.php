<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:26:12 +0800
 */

namespace Teddy\Console\Commands\Workerman;

use Symfony\Component\Console\Input\InputOption;

class RestartCommand extends DefaultCommand
{
    protected string $action = 'restart';

    protected function configure(): void
    {
        $this->setName('workerman:restart')
            ->setDefinition([
                new InputOption('daemon', 'd', InputOption::VALUE_NONE, 'Start in DAEMON mode'),
                new InputOption('gracefully', 'g', InputOption::VALUE_NONE, 'Stop gracefully'),
            ])
            ->setDescription('Restart workerman workers')
        ;
    }
}
