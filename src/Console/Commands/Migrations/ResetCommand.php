<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 15:14:18 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class ResetCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('migrate:reset')
            ->setDescription('Rollback all database migrations')
        ;
    }

    protected function handle()
    {
        if (!$this->getMigrator()->repositoryExists()) {
            return $this->error('No migrations found.');
        }

        $this->getMigrator()->reset($this->getMigrationPath());
    }
}
