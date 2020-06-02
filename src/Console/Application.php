<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-02 12:15:46 +0800
 */

namespace Teddy\Console;

use fwkit\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teddy\Abstracts\AbstractApp;
use Teddy\Console\Commands\Migrations;
use Teddy\Console\Commands\Models;
use Teddy\Console\Commands\ServerStartCommand;

class Application extends ConsoleApplication
{
    protected $app;

    protected $version;

    public function __construct(AbstractApp $app)
    {
        $version = config('app.version') ?: 'UNKNOWN';
        parent::__construct('Teddy Framework', $version);
        $this->app = $app;
        $this->version = $version;

        $this->addCommands([
            new ServerStartCommand,
            new Migrations\MigrationMakeCommand,
            new Migrations\InstallCommand,
            new Migrations\MigrateCommand,
            new Migrations\RefreshCommand,
            new Migrations\ResetCommand,
            new Migrations\RollbackCommand,
            new Migrations\StatusCommand,
            new Models\ModelMakeCommand,
        ]);

        $commandList = config('command.list', []);
        if (!empty($commandList) && is_array($commandList)) {
            $this->addCommands($commandList);
        }

        $defaultCommand = config('command.default', 'start');
        $this->setDefaultCommand($defaultCommand);
    }

    protected function welcome(InputInterface $input, OutputInterface $output): void
    {
        $appVersion = $this->version;
        $phpVersion = PHP_VERSION;
        $swooleVersion = SWOOLE_VERSION;

        $output->writeln(<<<EOL
             _____        _     _         ____  _   _ ____
            |_   _|__  __| | __| |_   _  |  _ \| | | |  _ \
              | |/ _ \/ _` |/ _` | | | | | |_) | |_| | |_) |
              | |  __/ (_| | (_| | |_| | |  __/|  _  |  __/
              |_|\___|\__,_|\__,_|\__, | |_|   |_| |_|_|
                                  |___/


            Application Version: {$appVersion}, PHP: {$phpVersion}, Swoole: {$swooleVersion}

            EOL);
    }
}
