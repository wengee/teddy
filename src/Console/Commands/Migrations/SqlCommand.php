<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 15:15:33 +0800
 */

namespace Teddy\Console\Commands\Migrations;

use Symfony\Component\Console\Input\InputOption;
use Teddy\Database\Schema\Schema;
use Teddy\Utils\FileSystem;

class SqlCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('migrate:sql')
            ->setDefinition([
                new InputOption('since', 's', InputOption::VALUE_OPTIONAL, 'Since the version'),
                new InputOption('file', 'f', InputOption::VALUE_OPTIONAL, 'Save sql to the file'),
            ])
            ->setDescription('Run the database migrations and output sql')
        ;
    }

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
                $name  = $migrator->getMigrationName($file)
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
