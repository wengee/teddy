<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class RefreshCommand extends BaseCommand
{
    protected $signature = 'migrate:refresh
        {--t|table= : The migration to refresh}';

    protected $description = 'Refresh the database migrations';

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
