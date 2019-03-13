<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-07 11:45:01 +0800
 */
namespace Teddy\Flysystem;

use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\FilesystemInterface;
use RuntimeException;

class FilesystemAdapter
{
    protected $driver;

    public function __construct(FilesystemInterface $driver)
    {
        $this->driver = $driver;
    }

    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->driver, $method], $parameters);
    }

    public function url($path)
    {
        $adapter = $this->driver->getAdapter();

        if (method_exists($adapter, 'getUrl')) {
            return $adapter->getUrl($path);
        } elseif (method_exists($this->driver, 'getUrl')) {
            return $this->driver->getUrl($path);
        } elseif ($adapter instanceof LocalAdapter) {
            return $this->getLocalUrl($path);
        } else {
            throw new RuntimeException('This driver does not support retrieving URLs.');
        }
    }

    protected function getLocalUrl($path)
    {
        $config = $this->driver->getConfig();
        if ($config->has('url')) {
            return $this->concatPathToUrl($config->get('url'), $path);
        }

        return $path;
    }

    protected function concatPathToUrl($url, $path)
    {
        return rtrim($url, '/').'/'.ltrim($path, '/');
    }
}
