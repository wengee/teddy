<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-12 16:37:56 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class ResetCommand extends BaseCommand
{
    protected $name = 'migrate:reset';

    protected $description = 'Rollback all database migrations';

    protected function handle()
    {
        if (!$this->getMigrator()->repositoryExists()) {
            return $this->error('No migrations found.');
        }

        $this->getMigrator()->reset($this->getMigrationPath());
    }
}
