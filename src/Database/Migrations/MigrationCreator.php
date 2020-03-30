<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-30 10:32:14 +0800
 */

namespace Teddy\Database\Migrations;

use Illuminate\Support\Str;
use Teddy\Utils\FileSystem;

class MigrationCreator
{
    /**
     * Create a new migration at the given path.
     *
     * @throws \Exception
     */
    public function create(string $name, string $path, ?string $table = null, bool $create = false): string
    {
        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $number = $this->getMaxNumber($path) + 1;
        file_put_contents(
            $path = $this->getPath($name, $path, $number),
            $this->populateStub($name, $stub, $table, $number)
        );

        return $path;
    }

    /**
     * Get the path to the stubs.
     */
    public function stubPaths(): array
    {
        return [
            path_join(app()->getBasePath(), '.stubs', 'migrations'),
            system_path('_stubs', 'migrations'),
        ];
    }

    /**
     * Get the migration stub file.
     */
    protected function getStub(?string $table = null, bool $create = true): string
    {
        if ($table === null) {
            return FileSystem::getContents($this->stubPaths(), 'blank.stub');
        }

        // We also have stubs for creating new tables and modifying existing tables
        // to save the developer some typing when they are creating a new tables
        // or modifying existing tables. We'll grab the appropriate stub here.
        $stub = $create ? 'create.stub' : 'update.stub';

        return FileSystem::getContents($this->stubPaths(), "/{$stub}");
    }

    /**
     * Populate the place-holders in the migration stub.
     */
    protected function populateStub(string $name, string $stub, ?string $table = null, int $number = 0): string
    {
        $stub = str_replace('DummyClass', $this->getClassName($name, $number), $stub);

        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
        if ($table !== null) {
            $stub = str_replace('DummyTable', $table, $stub);
        }

        return $stub;
    }

    /**
     * Get the class name of a migration name.
     */
    protected function getClassName(string $name, int $number): string
    {
        return Str::studly($name) . 'Migration_' . $this->getPrefix($number);
    }

    /**
     * Get the full path to the migration.
     */
    protected function getPath(string $name, string $path, int $number = 0): string
    {
        return $path . '/' . $this->getPrefix($number) . '_' . $name . '.php';
    }

    protected function getPrefix(int $number = 0): string
    {
        return sprintf('%04d', $number);
    }

    protected function getMaxNumber(string $path): int
    {
        $ret = 0;
        if (is_dir($path) && ($dh = opendir($path))) {
            while (($file = readdir($dh)) !== false) {
                if (substr($file, -4) === '.php') {
                    $ret = max($ret, intval($file));
                }
            }
        }

        return $ret;
    }
}
