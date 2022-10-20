<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 15:15:55 +0800
 */

namespace Teddy\Console\Commands\Migrations;

use Illuminate\Support\Collection;

class StatusCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('migrate:status')
            ->setDescription('Show the status of each migration')
        ;
    }

    protected function handle()
    {
        if (!$this->getMigrator()->repositoryExists()) {
            return $this->error('No migrations found.');
        }

        $ran     = $this->getMigrator()->getRepository()->getRan();
        $batches = $this->getMigrator()->getRepository()->getMigrationBatches();

        if (count($migrations = $this->getStatusFor($ran, $batches)) > 0) {
            $this->table(['Ran?', 'Migration', 'Batch'], $migrations);
        } else {
            $this->error('No migrations found');
        }
    }

    protected function getStatusFor(array $ran, array $batches)
    {
        return Collection::make($this->getAllMigrationFiles())
            ->map(function ($migration) use ($ran, $batches) {
                $migrationName = $this->getMigrator()->getMigrationName($migration);

                return in_array($migrationName, $ran)
                                ? ['<info>Y</info>', $migrationName, $batches[$migrationName]]
                                : ['<fg=red>N</fg=red>', $migrationName];
            })->all();
    }

    protected function getAllMigrationFiles()
    {
        return $this->getMigrator()->getMigrationFiles($this->getMigrationPath());
    }
}
