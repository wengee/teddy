<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-05 12:19:03 +0800
 */

namespace Teddy\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStartCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('server:start')
            ->setDefinition($this->createDefinition())
            ->setDescription('Start server')
            ->setHelp('');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        app()->listen();
    }

    private function createDefinition(): InputDefinition
    {
        return new InputDefinition([]);
    }
}
