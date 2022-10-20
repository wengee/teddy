<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 15:14:00 +0800
 */

namespace Teddy\Console\Commands\Migrations;

use Symfony\Component\Console\Input\InputOption;

class RefreshCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('migrate:refresh')
            ->setDefinition([
                new InputOption('table', 't', InputOption::VALUE_OPTIONAL, 'The migration to refresh'),
            ])
            ->setDescription('Refresh the database migrations')
        ;
    }

    protected function handle(): void
    {
        $this->prepareDatabase();
        $table = $this->option('table');

        $this->getMigrator()->refresh(
            $this->getMigrationPath(),
            $table
        );
    }

    protected function prepareDatabase(): void
    {
        if (!$this->getMigrator()->repositoryExists()) {
            $this->getMigrator()->getRepository()->createRepository();
        }
    }
}
