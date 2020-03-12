<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-12 16:03:09 +0800
 */

namespace Teddy\Console\Commands\Migrations;

use Teddy\Console\Command;
use Teddy\Database\Migrations\MigrationCreator;

class MigrationMakeCommand extends Command
{
    protected $signature = 'make:migration {name : The name of the migration}
        {--c|create= : The table to be created}
        {--t|table= : The table to migrate}';

    protected function handle()
    {
        $file = pathinfo(make(MigrationCreator::class)->create(
            $this->argument('name'),
            path_join(app()->getBasePath(), 'migrations'),
            $this->option('table'),
            (bool) $this->option('create')
        ), PATHINFO_FILENAME);

        $this->output->writeln("<info>Created Migration:</info> {$file}");
        return 0;
    }
}
