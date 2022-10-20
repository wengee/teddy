<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 15:17:38 +0800
 */

namespace Teddy\Console\Commands\Migrations;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Teddy\Abstracts\AbstractCommand;
use Teddy\Database\Migrations\MigrationCreator;

class MigrationMakeCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('make:migration')
            ->setDefinition([
                new InputArgument('name', InputArgument::REQUIRED, 'The name of the migration'),
                new InputOption('create', 'c', InputOption::VALUE_NONE, 'The table to be created'),
                new InputOption('table', 't', InputOption::VALUE_OPTIONAL, 'The table to migrate'),
            ])
            ->setDescription('Generate a migration file')
        ;
    }

    protected function handle(): void
    {
        $name   = $this->argument('name');
        $table  = $this->option('table');
        $create = (bool) $this->option('create');
        if ($create) {
            $table = $table ?: $name;
        }

        $file = pathinfo(make(MigrationCreator::class)->create(
            $name,
            base_path('migrations'),
            $table,
            $create
        ), PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> {$file}");
    }
}
