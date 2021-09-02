<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-30 16:32:49 +0800
 */

namespace Teddy\Console\Commands\Migrations;

use Teddy\Database\Schema\Schema;
use Teddy\Utils\FileSystem;

class SqlCommand extends BaseCommand
{
    protected $signature = 'migrate:sql
        {--s|since= : Since the version}
        {--f|file= : Save sql to the file}';

    protected $description = 'Run the database migrations and output sql';

    protected function handle(): void
    {
        $f    = null;
        $file = $this->option('file');
        if ($file) {
            if ('/' !== $file[0] || '\\' !== $file[0]) {
                $file = FileSystem::joinPath(getcwd(), $file);
            }

            $f = fopen($file, 'w');
        }

        Schema::callback(function (array $sql) use ($f): void {
            foreach ($sql as $line) {
                if ($f) {
                    fwrite($f, $line.";\n");
                } else {
                    $this->line($line.';');
                }
            }
        });

        $since    = $this->option('since');
        $migrator = $this->getMigrator();
        $files    = $migrator->getMigrationFiles($this->getMigrationPath());
        foreach ($files as $file) {
            $migration = $migrator->resolve(
                $name = $migrator->getMigrationName($file)
            );

            if (!$since || version_compare($migration->getVersion(), $since, '>')) {
                $migration->up();
            }
        }

        if ($f) {
            fclose($f);
        }
        Schema::callback(null);
    }
}
