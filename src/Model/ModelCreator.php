<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-21 12:54:08 +0800
 */

namespace Teddy\Model;

use Teddy\Utils\FileSystem;

class ModelCreator
{
    public function create(string $path, string $name, string $table): string
    {
        $stub = $this->getStub();

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        file_put_contents(
            $path = $this->getPath($name, $path),
            $this->populateStub($stub, $name, $table)
        );

        return $path;
    }

    public function stubPaths(): array
    {
        return [
            path_join(app()->getBasePath(), '.stubs'),
            __DIR__ . '/stubs',
        ];
    }

    protected function getStub(): string
    {
        return FileSystem::getContents($this->stubPaths, 'model.stub');
    }

    protected function populateStub(string $stub, string $name, string $table): string
    {
        $stub = str_replace('DummyClass', $name, $stub);
        $stub = str_replace('DummyTable', $table, $stub);

        return $stub;
    }

    protected function getPath(string $name, string $path): string
    {
        return $path . '/' . $name . '.php';
    }
}
