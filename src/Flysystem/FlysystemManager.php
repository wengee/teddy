<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-08 11:35:35 +0800
 */
namespace SlimExtra\Flysystem;

use InvalidArgumentException;
use Jacobcyl\AliOSS\Plugins\PutFile;
use Jacobcyl\AliOSS\Plugins\PutRemoteFile;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use OSS\OssClient;

class FlysystemManager
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $disks = [];

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    public function disk(?string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->disks[$name] = $this->get($name);
    }

    protected function get($name)
    {
        return $this->disks[$name] ?? $this->resolve($name);
    }

    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

    public function createLocalDriver(array $config)
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

    public function createFtpDriver(array $config)
    {
        return $this->adapt($this->createFlysystem(
            new FtpAdapter($config),
            $config
        ));
    }

    public function createOssDriver(array $config)
    {
        $accessId  = $config['access_id'];
        $accessKey = $config['access_key'];
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

    protected function adapt(FilesystemInterface $filesystem)
    {
        return new FilesystemAdapter($filesystem);
    }

    protected function createFlysystem(AdapterInterface $adapter, array $config)
    {
        return new Filesystem($adapter, count($config) > 0 ? $config : null);
    }

    protected function getConfig($name)
    {
        return array_get($this->settings, "disks.{$name}");
    }

    public function getDefaultDriver()
    {
        return array_get($this->settings, 'default', 'default');
    }

    public function __call($method, $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }
}
