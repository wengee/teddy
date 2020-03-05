<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-05 12:05:32 +0800
 */

namespace Teddy\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('serv')
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
