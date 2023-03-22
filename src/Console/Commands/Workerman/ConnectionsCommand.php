<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:26:05 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class ConnectionsCommand extends DefaultCommand
{
    protected string $action = 'connections';

    protected function configure(): void
    {
        $this->setName('workerman:connections')
            ->setDescription('Show workerman connections')
        ;
    }
}
