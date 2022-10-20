<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 15:10:10 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class MigrateCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('migrate')
            ->setDescription('Run the database migrations')
        ;
    }

    protected function handle(): void
    {
        $this->prepareDatabase();
        $this->getMigrator()->run($this->getMigrationPath());
    }

    protected function prepareDatabase(): void
    {
        if (!$this->getMigrator()->repositoryExists()) {
            $this->getMigrator()->getRepository()->createRepository();
        }
    }
}
