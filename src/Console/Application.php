<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-20 23:24:30 +0800
 */

namespace Teddy\Console;

use Teddy\Abstracts\AbstractApp;
use Teddy\Console\Commands\Models;
use Teddy\Console\Commands\Migrations;
use Teddy\Console\Commands\ServerStartCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    protected $app;

    public function __construct(AbstractApp $app, ?string $version = null)
    {
        $version = $version ?: config('app.version', 'UNKNOWN');
        parent::__construct('Teddy Framework', $version);
        $this->app = $app;

        $this->addCommands([
            new ServerStartCommand,
            new Migrations\MigrationMakeCommand,
            new Migrations\InstallCommand,
            new Migrations\MigrateCommand,
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
}
