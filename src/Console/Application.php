<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-05 18:08:51 +0800
 */

namespace Teddy\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Teddy\Abstracts\AbstractApp;
use Teddy\Console\Commands\ServerStartCommand;

class Application extends SymfonyApplication
{
    protected $app;

    public function __construct(AbstractApp $app, ?string $version = null)
    {
        $version = $version ?: config('app.version', 'UNKNOWN');
        parent::__construct('Teddy Framework', $version);
        $this->app = $app;

        $this->add(new ServerStartCommand);
        $commandList = config('command.list', []);
        if (!empty($commandList) && is_array($commandList)) {
            $this->addCommands($commandList);
        }

        $defaultCommand = config('command.default', 'server:start');
        $this->setDefaultCommand($defaultCommand);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $input = $input ?? new ArgvInput;
        $output = $output ?? new ConsoleOutput;
        return parent::run($input, new SymfonyStyle($input, $output));
    }
}
