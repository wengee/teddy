<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 14:36:38 +0800
 */

namespace Teddy\Console\Commands;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Teddy\Abstracts\AbstractCommand;
use Teddy\Runtime;

class StartCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('start')
            ->setDefinition([
                new InputArgument('server', InputArgument::OPTIONAL, 'Server type'),
            ])
            ->setDescription('Start server')
        ;
    }

    protected function handle(): void
    {
        $commandName = 'workerman:start';
        if (Runtime::isSwoole()) {
            $commandName = 'swoole:start';
        } elseif (Runtime::isWorkerman()) {
            $commandName = 'workerman:start';
        } elseif ($server = $this->argument('server')) {
            $commandName = $server.':start';
        }

        $command = $this->getApplication()->find($commandName);
        if (!$command) {
            $this->error('Unknown server type.');

            return;
        }

        $greetInput = new ArrayInput([
            'command' => $commandName,
        ]);
        $command->run($greetInput, $this->output);
    }
}
