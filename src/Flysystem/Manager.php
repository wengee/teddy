<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-16 17:00:43 +0800
 */

namespace Teddy\Flysystem;

use InvalidArgumentException;
use Jacobcyl\AliOSS\Plugins\PutFile;
use Jacobcyl\AliOSS\Plugins\PutRemoteFile;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use OSS\OssClient;
use Overtrue\Flysystem\Cos\CosAdapter;

class Manager
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $disks = [];

    public function __construct()
    {
        $config = config('flysystem');
        if ($config && is_array($config)) {
            $this->config = $config;
        }
    }

    public function disk(?string $name = null): FilesystemAdapter
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->disks[$name] = $this->get($name);
    }

    protected function get($name): FilesystemAdapter
    {
        return $this->disks[$name] ?? $this->resolve($name);
    }

    protected function resolve($name): FilesystemAdapter
    {
        $config = $this->getConfig($name);
        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

    public function createLocalDriver(array $config): FilesystemAdapter
    {
        $permissions = $config['permissions'] ?? [];

        $links = ($config['links'] ?? null) === 'skip'
            ? LocalAdapter::SKIP_LINKS
            : LocalAdapter::DISALLOW_LINKS;

        return $this->adapt($this->createFlysystem(new LocalAdapter(
            $config['root'],
            LOCK_EX,
            $links,
            $permissions
        ), $config));
    }

    public function createFtpDriver(array $config): FilesystemAdapter
    {
        return $this->adapt($this->createFlysystem(
            new FtpAdapter($config),
            $config
        ));
    }

    public function createOssDriver(array $config): FilesystemAdapter
    {
        $accessId  = $config['accessId'];
        $accessKey = $config['accessKey'];
        $cdnDomain = empty($config['cdnDomain']) ? '' : $config['cdnDomain'];
        $bucket    = $config['bucket'];
        $ssl       = empty($config['ssl']) ? false : $config['ssl'];
        $isCname   = empty($config['isCName']) ? false : $config['isCName'];
        $debug     = empty($config['debug']) ? false : $config['debug'];
        $endPoint  = $config['endpoint'];
        $epInternal= $isCname ? $cdnDomain : (empty($config['endpointInternal']) ? $endPoint : $config['endpointInternal']);
        $prefix    = empty($config['prefix']) ? null : $config['prefix'];
        $options   = empty($config['options']) ? [] : $config['options'];

        $client  = new OssClient($accessId, $accessKey, $epInternal, $isCname);
        $adapter = new AliOssAdapter($client, $bucket, $endPoint, $ssl, $isCname, $debug, $cdnDomain, $prefix, $options);
        $filesystem =  new Filesystem($adapter);

        $filesystem->addPlugin(new PutFile());
        $filesystem->addPlugin(new PutRemoteFile());
        //$filesystem->addPlugin(new CallBack());
        return $this->adapt($filesystem);
    }

    public function createCosDriver(array $config): FilesystemAdapter
    {
        $cosConf = [
            'region'            => $config['region'],
            'credentials'       => [
                'appId'         => $config['appId'],
                'secretId'      => $config['secretId'],
                'secretKey'     => $config['secretKey'],
            ],
            'bucket'            => $config['bucket'],
            'timeout'           => $config['timeout'] ?? 60,
            'connect_timeout'   => $config['connectTimeout'] ?? 60,
            'cdn'               => $config['cdnDomain'] ?? '',
            'scheme'            => $config['scheme'] ?? 'http',
            'read_from_cdn'     => $config['readFromCdn'] ?? false,
        ];

        $adapter = new CosAdapter($cosConf);
        $filesystem = new Filesystem($adapter);
        return $this->adapt($filesystem);
    }

    protected function adapt(FilesystemInterface $filesystem): FilesystemAdapter
    {
        return new FilesystemAdapter($filesystem);
    }

    protected function createFlysystem(AdapterInterface $adapter, array $config)
    {
        return new Filesystem($adapter, count($config) > 0 ? $config : null);
    }

    protected function getConfig($name)
    {
        return array_get($this->config, "disks.{$name}");
    }

    public function getDefaultDriver()
    {
        return array_get($this->config, 'default', 'default');
    }

    public function __call($method, $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }
}
