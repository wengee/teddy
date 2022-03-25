<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-25 11:37:28 +0800
 */

namespace Teddy\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teddy\Application as TeddyApplication;
use Teddy\Console\Commands\Migrations;
use Teddy\Console\Commands\Swoole;
use Teddy\Console\Commands\Workerman;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Traits\ContainerAwareTrait;

class Application extends SymfonyApplication implements ContainerAwareInterface
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

        /** add swoole commands */
        if (extension_loaded('swoole') && class_exists('\\Swoole\\Http\\Server')) {
            $this->addCommands([
                new Swoole\StartCommand(),
            ]);
        }

        /** add workerman commands */
        if (class_exists('\\Workerman\\Worker')) {
            $this->addCommands([
                new Workerman\ConnectionsCommand(),
                new Workerman\ReloadCommand(),
                new Workerman\RestartCommand(),
                new Workerman\StartCommand(),
                new Workerman\StatusCommand(),
                new Workerman\StopCommand(),
            ]);
        }

        $this->addCommands([
            new Migrations\MigrationMakeCommand(),
            new Migrations\InstallCommand(),
            new Migrations\MigrateCommand(),
            new Migrations\RefreshCommand(),
            new Migrations\ResetCommand(),
            new Migrations\RollbackCommand(),
            new Migrations\StatusCommand(),
            new Migrations\SqlCommand(),
        ]);

        $commandList = config('command', []);
        if (!empty($commandList) && is_array($commandList)) {
            $this->addCommands($commandList);
        }
    }

    public function getApp(): TeddyApplication
    {
        return $this->app;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->welcome($input, $output);

        return parent::doRun($input, $output);
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
