<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-07 10:55:39 +0800
 */
namespace SlimExtra\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use SlimExtra\Flysystem\FlysystemManager;

class FlysystemProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $settings = (array) $container['settings']->get('flysystem');
        if ($settings) {
            $container['fs'] = new FlysystemManager($settings);
        }
    }
}
