<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-20 23:51:03 +0800
 */

namespace Teddy\Model;

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

    protected function getStub(): string
    {
        $stubPaths = [
            path_join(app()->getBasePath(), '.stubs'),
            __DIR__ . '/stubs',
        ];

        foreach ($stubPaths as $path) {
            $file = path_join($path, 'model.stub');
            if (is_file($file)) {
                return file_get_contents($file);
            }
        }
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
