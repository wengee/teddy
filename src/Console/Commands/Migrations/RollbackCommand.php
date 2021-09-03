<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class RollbackCommand extends BaseCommand
{
    protected $name = 'migrate:rollback';

    protected $description = 'Rollback the last database migration';

    protected function handle()
    {
        if (!$this->getMigrator()->repositoryExists()) {
            return $this->error('No migrations found.');
        }

        $this->getMigrator()->rollback($this->getMigrationPath());
    }
}
