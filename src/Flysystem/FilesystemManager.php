<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 15:53:13 +0800
 */

namespace Teddy\Flysystem;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Teddy\Flysystem\Adapters\CosAdapter;
use Teddy\Flysystem\Adapters\LocalAdapter;
use Teddy\Flysystem\Adapters\OssAdapter;
use Teddy\Interfaces\FilesystemAdapter;

class FilesystemManager
{
    protected array $config = [];

    /**
     * @var Filesystem[]
     */
    protected array $disks = [];

    public function __construct()
    {
        $this->config = config('flysystem');
    }

    public function __call($method, $parameters)
    {
        return $this->disk()->{$method}(...$parameters);
    }

    public function disk(?string $name = null): Filesystem
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->disks[$name] = $this->resolve($name);
    }

    protected function resolve(string $name): Filesystem
    {
        if (isset($this->disks[$name])) {
            return $this->disks[$name];
        }

        $config = $this->getConfig($name);
        if (!$config) {
            throw new InvalidArgumentException("Disk [{$name}] is not found.");
        }

        if (is_string($config)) {
            return $this->resolve($config);
        }

        $driver       = $config['driver'] ?? 'local';
        $driverMethod = 'create'.ucfirst($driver).'FileSystem';
        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }

        throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
    }

    protected function createLocalFileSystem(array $config): Filesystem
    {
        return $this->createFlysystem(new LocalAdapter($config), $config);
    }

    protected function createOssFileSystem(array $config): Filesystem
    {
        return $this->createFlysystem(new OssAdapter($config), $config);
    }

    protected function createCosFileSystem(array $config): Filesystem
    {
        return $this->createFlysystem(new CosAdapter($config), $config);
    }

    protected function createFlysystem(FilesystemAdapter $adapter, array $config): Filesystem
    {
        return new Filesystem($adapter, $config);
    }

    protected function getConfig(string $name)
    {
        $alias = $this->config[$name] ?? null;
        if ($alias && is_string($alias)) {
            $name = $alias;
        }

        return Arr::get($this->config, "disks.{$name}");
    }

    protected function getDefaultDriver(): string
    {
        return $this->config['default'] ?? 'default';
    }
}
