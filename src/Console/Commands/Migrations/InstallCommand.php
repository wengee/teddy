<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 15:08:55 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class InstallCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('migrate:install')
            ->setDescription('Create the migration repository')
        ;
    }

    protected function handle(): void
    {
        if (!$this->getMigrator()->repositoryExists()) {
            $this->getMigrator()->getRepository()->createRepository();
        }

        $this->info('Migration table created successfully.');
    }
}
