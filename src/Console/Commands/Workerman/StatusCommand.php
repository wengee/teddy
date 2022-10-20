<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 14:44:33 +0800
 */

namespace Teddy\Console\Commands\Workerman;

use Symfony\Component\Console\Input\InputOption;

class StatusCommand extends DefaultCommand
{
    protected $action = 'status';

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
