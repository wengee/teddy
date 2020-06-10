<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-10 11:19:19 +0800
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
        parent::__construct('');
        $this->app = $app;
        $this->version = config('app.version') ?: 'UNKNOWN';

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

    public function getApp(): AbstractApp
    {
        return $this->app;
    }

    protected function welcome(InputInterface $input, OutputInterface $output): void
    {
        $appName = $this->app->getName();
        $appVersion = $this->version;
        $os = PHP_OS;
        $phpVersion = PHP_VERSION;
        $swooleVersion = defined('SWOOLE_VERSION') ? SWOOLE_VERSION : 'UNKNOWN';

        $output->writeln(<<<EOL
             _____        _     _         ____  _   _ ____
            |_   _|__  __| | __| |_   _  |  _ \| | | |  _ \
              | |/ _ \/ _` |/ _` | | | | | |_) | |_| | |_) |
              | |  __/ (_| | (_| | |_| | |  __/|  _  |  __/
              |_|\___|\__,_|\__,_|\__, | |_|   |_| |_|_|
                                  |___/

            OS: <info>{$os}</info>, PHP: <info>{$phpVersion}</info>, Swoole: <info>{$swooleVersion}</info>
            Application: <info>{$appName}</info>, Version: <info>{$appVersion}</info>

            EOL);
    }
}
