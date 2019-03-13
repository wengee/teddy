<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-11 16:22:40 +0800
 */
namespace SlimExtra\Traits;

use Interop\Container\ContainerInterface;

trait HasContainer
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Collection
     */
    protected $settings;

    public function setContainer(?ContainerInterface $container = null)
    {
        if ($container) {
            $this->container = $container;
            $this->settings = $container->get('settings') ?: [];
        }
    }

    protected function getSetting($key, $default = null)
    {
        return array_get($this->settings, $key, $default);
    }
}
