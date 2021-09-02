<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-27 17:31:16 +0800
 */

namespace Teddy\Console;

use fwkit\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teddy\Application as TeddyApplication;
use Teddy\Console\Commands\Migrations;
use Teddy\Console\Commands\StartCommand;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Traits\ContainerAwareTrait;

class Application extends ConsoleApplication implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var TeddyApplication */
    protected $app;

    /** @var string */
    protected $appName;

    /** @var string */
    protected $version;

    public function __construct(TeddyApplication $app)
    {
        parent::__construct('');
        $this->app     = $app;
        $this->appName = config('app.name') ?: 'Teddy App';
        $this->version = config('app.version') ?: 'UNKNOWN';

        $this->addCommands([
            new StartCommand(),
            new Migrations\MigrationMakeCommand(),
            new Migrations\InstallCommand(),
            new Migrations\MigrateCommand(),
            new Migrations\RefreshCommand(),
            new Migrations\ResetCommand(),
            new Migrations\RollbackCommand(),
            new Migrations\StatusCommand(),
            new Migrations\SqlCommand(),
        ]);

        $commandList = config('command.list', []);
        if (!empty($commandList) && is_array($commandList)) {
            $this->addCommands($commandList);
        }

        $defaultCommand = config('command.default', 'start');
        $this->setDefaultCommand($defaultCommand);
    }

    public function getApp(): TeddyApplication
    {
        return $this->app;
    }

    protected function welcome(InputInterface $input, OutputInterface $output): void
    {
        $appName       = $this->appName;
        $appVersion    = $this->version;
        $os            = PHP_OS;
        $phpVersion    = PHP_VERSION;
        $swooleVersion = defined('SWOOLE_VERSION') ? SWOOLE_VERSION : 'UNKNOWN';

        $output->writeln(<<<EOL
             _____        _     _         ____  _   _ ____
            |_   _|__  __| | __| |_   _  |  _ \\| | | |  _ \\
              | |/ _ \\/ _` |/ _` | | | | | |_) | |_| | |_) |
              | |  __/ (_| | (_| | |_| | |  __/|  _  |  __/
              |_|\\___|\\__,_|\\__,_|\\__, | |_|   |_| |_|_|
                                  |___/

            OS: <info>{$os}</info>, PHP: <info>{$phpVersion}</info>, Swoole: <info>{$swooleVersion}</info>
            Application: <info>{$appName}</info>, Version: <info>{$appVersion}</info>

            EOL);
    }
}
