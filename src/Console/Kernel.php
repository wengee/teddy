<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-14 17:19:30 +0800
 */

namespace Teddy\Console;

use Symfony\Component\Console\Application as ConsoleApplication;
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

    /**
     * @var ConsoleApplication
     */
    protected $console;

    /**
     * @var string
     */
    protected $appName;

    /**
     * @var string
     */
    protected $version;

    public function __construct()
    {
        $this->appName = config('app.name') ?: 'Teddy App';
        $this->version = config('app.version') ?: 'UNKNOWN';

        $console = $this->getConsole();

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
    }

    public function handle(InputInterface $input, ?OutputInterface $output = null): void
    {
        $appName    = $this->appName;
        $appVersion = $this->version;
        $os         = PHP_OS;
        $phpVersion = PHP_VERSION;

        $output->writeln(<<<EOL
             _____        _     _         ____  _   _ ____
            |_   _|__  __| | __| |_   _  |  _ \\| | | |  _ \\
              | |/ _ \\/ _` |/ _` | | | | | |_) | |_| | |_) |
              | |  __/ (_| | (_| | |_| | |  __/|  _  |  __/
              |_|\\___|\\__,_|\\__,_|\\__, | |_|   |_| |_|_|
                                  |___/

            OS: <info>{$os}</info>, PHP: <info>{$phpVersion}</info>
            Application: <info>{$appName}</info>, Version: <info>{$appVersion}</info>

            EOL);

        $this->getConsole()->run($input, $output);
    }

    protected function getConsole(): ConsoleApplication
    {
        if (null === $this->console) {
            $this->console = new ConsoleApplication('');
        }

        return $this->console;
    }
}
