<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 10:58:45 +0800
 */

namespace Teddy\Database\Migrations;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Teddy\Console\Command;

class Migrator
{
    protected MigrationRepository $repository;

    protected ?Command $command;

    protected array $notes = [];

    public function __construct()
    {
        $this->repository = new MigrationRepository();
    }

    public function setCommand(Command $command): void
    {
        $this->command = $command;
    }

    public function run(string $path)
    {
        $this->notes = [];

        $files      = $this->getMigrationFiles($path);
        $migrations = $this->pendingMigrations(
            $files,
            $this->repository->getRan()
        );

        $this->runPending($migrations);

        return $migrations;
    }

    public function runPending(array $migrations): void
    {
        if (0 === count($migrations)) {
            $this->note('<info>Nothing to migrate.</info>');

            return;
        }

        $batch = $this->repository->getNextBatchNumber();
        foreach ($migrations as $file) {
            $this->runUp($file, $batch);
        }
    }

    public function refresh(string $path, ?string $table = null): void
    {
        if (!$table) {
            $this->reset($path);
            $this->run($path);
        } else {
            $files = $this->getMigrationFiles($path, $table);
            if (0 === count($files)) {
                $this->note('<info>Nothing to migrate.</info>');

                return;
            }

            $ran             = $this->repository->getRan();
            $resetMigrations = Collection::make($files)
                ->filter(function ($value, $key) use ($ran) {
                    return in_array($key, $ran, true);
                })->keys()->map(function ($m) {
                    return (object) ['migration' => $m];
                })->all();

            $this->rollbackMigrations($resetMigrations, $path);
            $this->runPending($files);
        }
    }

    public function rollback(string $path)
    {
        $this->notes = [];
        $migrations  = $this->repository->getLast();

        if (0 === count($migrations)) {
            $this->note('<info>Nothing to rollback.</info>');

            return [];
        }

        return $this->rollbackMigrations($migrations, $path);
    }

    public function reset(string $path)
    {
        $this->notes = [];
        $migrations  = array_reverse($this->repository->getRan());

        if (0 === count($migrations)) {
            $this->note('<info>Nothing to rollback.</info>');

            return [];
        }

        return $this->resetMigrations($migrations, $path);
    }

    public function getRepository(): MigrationRepository
    {
        return $this->repository;
    }

    public function repositoryExists()
    {
        return $this->repository->repositoryExists();
    }

    public function getNotes(): array
    {
        return $this->notes;
    }

    public function resolve($file)
    {
        if (preg_match('#^(\\d{8})_(\\d{6})_(.+)$#i', $file, $m)) {
            $class = Str::studly($m[3]).'Migration_'.$m[1].$m[2];
        } elseif (preg_match('#^(\\d+)_([^\\d].+)$#i', $file, $m)) {
            $class = Str::studly($m[2]).'Migration_'.$m[1];
        } else {
            $class = Str::studly($file);
        }

        return new $class();
    }

    public function getMigrationFiles($path, ?string $search = null): array
    {
        $ret = [];
        if (!is_dir($path)) {
            return $ret;
        }

        if ($dh = opendir($path)) {
            while (($file = readdir($dh)) !== false) {
                if ('.php' === substr($file, -4)) {
                    if ($search) {
                        $arr = explode('_', substr($file, 0, -4), 2);
                        if (2 !== count($arr)) {
                            continue;
                        }

                        $isInt = $this->isInteger($search);
                        if (($isInt && intval($search) !== intval($arr[0])) || (!$isInt && !Str::is($search, $arr[1]))) {
                            continue;
                        }
                    }

                    $realpath = path_join($path, $file);
                    $this->requireFile($realpath);
                    $ret[$this->getMigrationName($file)] = $realpath;
                }
            }

            closedir($dh);
        }

        ksort($ret, SORT_NATURAL);

        return $ret;
    }

    public function requireFile(string $file): void
    {
        if (is_file($file)) {
            require_once $file;
        }
    }

    public function getMigrationName($path)
    {
        return str_replace('.php', '', basename($path));
    }

    protected function pendingMigrations($files, $ran)
    {
        return Collection::make($files)
            ->reject(function ($file) use ($ran) {
                return in_array($this->getMigrationName($file), $ran);
            })->values()->all();
    }

    protected function runUp(string $file, int $batch): void
    {
        $migration = $this->resolve(
            $name  = $this->getMigrationName($file)
        );

        $this->note("<comment>Migrating:</comment> {$name}");
        $this->runMigration($migration, 'up');

        $batch = $migration->getBatch() ?: $batch;
        $this->repository->log($name, $batch);
        $this->note("<info>Migrated:</info>  {$name}");
    }

    protected function runDown(string $file, string $migration): void
    {
        $instance = $this->resolve(
            $name = $this->getMigrationName($file)
        );

        $this->note("<comment>Rolling back:</comment> {$name}");
        $this->runMigration($instance, 'down');
        $this->repository->delete($migration);
        $this->note("<info>Rolled back:</info>  {$name}");
    }

    protected function runMigration($migration, $method): void
    {
        if (method_exists($migration, $method)) {
            $migration->{$method}();
        }
    }

    protected function rollbackMigrations(array $migrations, string $path)
    {
        $rolledBack = [];
        $files      = $this->getMigrationFiles($path);

        foreach ($migrations as $migration) {
            $migration = (object) $migration;

            if (!$file = Arr::get($files, $migration->migration)) {
                $this->note("<fg=red>Migration not found:</> {$migration->migration}");

                continue;
            }

            $rolledBack[] = $file;
            $this->runDown($file, $migration->migration);
        }

        return $rolledBack;
    }

    protected function resetMigrations(array $migrations, string $path)
    {
        $migrations = collect($migrations)->map(function ($m) {
            return (object) ['migration' => $m];
        })->all();

        return $this->rollbackMigrations($migrations, $path);
    }

    protected function note($message): void
    {
        $this->notes[] = $message;
        if ($this->command) {
            $this->command->line($message);
        }
    }

    protected function isInteger($input): bool
    {
        return ctype_digit(strval($input));
    }
}
