<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 14:39:14 +0800
 */

namespace Teddy\Console\Commands\Workerman;

class ConnectionsCommand extends DefaultCommand
{
    protected $action = 'connections';

    protected function configure(): void
    {
        $this->setName('workerman:connections')
            ->setDescription('Show workerman connections')
        ;
    }
}
