<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-12 15:16:00 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class RollbackCommand extends BaseCommand
{
    protected $name = 'migrate:rollback';

    protected $description = 'Rollback the last database migration';

    protected function handle()
    {
        if (!$this->getMigrator()->repositoryExists()) {
            $this->error('No migrations found.');
            return 0;
        }

        $this->getMigrator()->rollback($this->getMigrationPath());
        foreach ($this->getMigrator()->getNotes() as $note) {
            $this->output->writeln($note);
        }

        return 0;
    }
}
