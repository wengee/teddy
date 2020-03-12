<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-12 15:00:12 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class MigrateCommand extends BaseCommand
{
    protected $name = 'migrate';

    protected $description = 'Run the database migrations';

    protected function handle(): void
    {
        $this->prepareDatabase();
        $this->getMigrator()->run($this->getMigrationPath());

        foreach ($this->getMigrator()->getNotes() as $note) {
            $this->output->writeln($note);
        }
    }

    protected function prepareDatabase(): void
    {
        if (!$this->getMigrator()->repositoryExists()) {
            $this->getMigrator()->getRepository()->createRepository();
        }
    }
}
