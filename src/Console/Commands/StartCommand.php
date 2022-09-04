<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-04 16:16:21 +0800
 */

namespace Teddy\Console\Commands;

use Symfony\Component\Console\Input\ArrayInput;
use Teddy\Console\Command;
use Teddy\Runtime;

class StartCommand extends Command
{
    protected $signature = 'start {server? : Server type}';

    protected $description = 'Start server';

    protected function handle(): void
    {
        $commandName = null;
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
