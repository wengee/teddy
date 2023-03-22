<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:26:22 +0800
 */

namespace Teddy\Console\Commands\Workerman;

use Symfony\Component\Console\Input\InputOption;

class StatusCommand extends DefaultCommand
{
    protected string $action = 'status';

    protected function configure(): void
    {
        $this->setName('workerman:status')
            ->setDefinition([
                new InputOption('live', 'l', InputOption::VALUE_NONE, 'Show live status'),
            ])
            ->setDescription('Show workerman status')
        ;
    }
}
