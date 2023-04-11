<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-11 11:38:13 +0800
 */

namespace Teddy\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teddy\Console\Commands\Migrations;
use Teddy\Console\Commands\StartCommand;
use Teddy\Console\Commands\Swoole;
use Teddy\Console\Commands\Workerman;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\KernelInterface;
use Teddy\Runtime;
use Teddy\Traits\ContainerAwareTrait;

class Kernel implements ContainerAwareInterface, KernelInterface
{
    use ContainerAwareTrait;

    protected Application $console;

    protected string $appName;

    protected string $version;

    public function __construct()
    {
        $this->appName = config('app.name') ?: 'Teddy App';
        $this->version = config('app.version') ?: 'UNKNOWN';

        $console = new Application('');

        // add swoole commands
        if (Runtime::swooleEnabled()) {
            $console->addCommands([
                new Swoole\StartCommand(),
            ]);
        }

        // add workerman commands
        if (Runtime::workermanEnabled()) {
            $console->addCommands([
                new Workerman\ConnectionsCommand(),
                new Workerman\ReloadCommand(),
                new Workerman\RestartCommand(),
                new Workerman\StartCommand(),
                new Workerman\StatusCommand(),
                new Workerman\StopCommand(),
            ]);
        }

        $console->addCommands([
            new Migrations\MigrationMakeCommand(),
            new Migrations\InstallCommand(),
            new Migrations\MigrateCommand(),
            new Migrations\RefreshCommand(),
            new Migrations\ResetCommand(),
            new Migrations\RollbackCommand(),
            new Migrations\StatusCommand(),
            new Migrations\SqlCommand(),

            new StartCommand(),
        ]);

        $commandList = config('command.list', []);
        if (!empty($commandList) && is_array($commandList)) {
            $console->addCommands($commandList);
        }

        if ($defaultCommand = config('command.default')) {
            $console->setDefaultCommand($defaultCommand);
        }

        $this->console = $console;
    }

    public function handle(InputInterface $input, OutputInterface $output = null): void
    {
        $appName    = $this->appName;
        $appVersion = $this->version;
        $os         = PHP_OS;
        $phpVersion = PHP_VERSION;

        $output->writeln(<<<EOL
            <info> _____        _     _         ____  _   _ ____
            |_   _|__  __| | __| |_   _  |  _ \\| | | |  _ \\
              | |/ _ \\/ _` |/ _` | | | | | |_) | |_| | |_) |
              | |  __/ (_| | (_| | |_| | |  __/|  _  |  __/
              |_|\\___|\\__,_|\\__,_|\\__, | |_|   |_| |_|_|
                                  |___/</info>

            OS: <comment>{$os}</comment>, PHP: <comment>{$phpVersion}</comment>
            Application: <comment>{$appName}</comment>, Version: <comment>{$appVersion}</comment>
            EOL);

        if ($input->hasParameterOption(['-V', '--version'], true)) {
            return;
        }

        $output->writeln('');
        $this->console->run($input, $output);
    }
}
