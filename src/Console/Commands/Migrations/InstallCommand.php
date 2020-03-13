<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-13 16:48:35 +0800
 */

namespace Teddy\Console\Commands\Migrations;

class InstallCommand extends BaseCommand
{
    protected $name = 'migrate:install';

    protected $description = 'Create the migration repository';

    protected function handle(): void
    {
        if (!$this->getMigrator()->repositoryExists()) {
            $this->getMigrator()->getRepository()->createRepository();
        }

        $this->info('Migration table created successfully.');
    }
}
