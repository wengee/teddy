<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-02 10:27:27 +0800
 */

namespace Teddy\Flysystem;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use League\Flysystem\FilesystemAdapter;
use Teddy\Flysystem\Adapters\CosAdapter;
use Teddy\Flysystem\Adapters\LocalAdapter;
use Teddy\Flysystem\Adapters\OssAdapter;

class Manager
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Filesystem[]
     */
    protected $disks = [];

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

    protected function resolve($name): Filesystem
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
        $cosConf = [
            'app_id'     => $config['app_id'] ?? ($config['appId'] ?? ''),
            'secret_id'  => $config['secret_id'] ?? ($config['secretId'] ?? ''),
            'secret_key' => $config['secret_key'] ?? ($config['secretKey'] ?? ''),
            'region'     => $config['region'] ?? '',
            'bucket'     => $config['bucket'] ?? '',
            'signed_url' => $config['signed_url'] ?? ($config['signedUrl'] ?? false),
            'cdn'        => $config['cdn'] ?? '',
        ];

        return $this->createFlysystem(new CosAdapter($cosConf), $config);
    }

    protected function createFlysystem(FilesystemAdapter $adapter, array $config): Filesystem
    {
        return new Filesystem($adapter, $config);
    }

    protected function getConfig($name)
    {
        return Arr::get($this->config, "disks.{$name}");
    }

    protected function getDefaultDriver(): string
    {
        return Arr::get($this->config, 'default', 'default');
    }
}
