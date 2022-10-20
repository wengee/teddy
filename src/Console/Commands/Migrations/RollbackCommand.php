<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 15:14:33 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class RollbackCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('migrate:rollback')
            ->setDescription('Rollback the last database migration')
        ;
    }

    protected function handle()
    {
        if (!$this->getMigrator()->repositoryExists()) {
            return $this->error('No migrations found.');
        }

        $this->getMigrator()->rollback($this->getMigrationPath());
    }
}
