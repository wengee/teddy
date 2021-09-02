<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-30 17:09:44 +0800
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
            base_path('.stubs'),
            system_path('_stubs'),
        ];
    }

    protected function getStub(): string
    {
        return FileSystem::getContents($this->stubPaths(), 'model.stub');
    }

    protected function populateStub(string $stub, string $name, string $table): string
    {
        $stub = str_replace('DummyClass', $name, $stub);

        return str_replace('DummyTable', $table, $stub);
    }

    protected function getPath(string $name, string $path): string
    {
        return $path.'/'.$name.'.php';
    }
}
