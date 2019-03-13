<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-07 09:53:27 +0800
 */
namespace Teddy\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Teddy\Jwt\JwtHelper;

class JwtProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $settings = (array) $container['settings']->get('jwt');
        $container['jwt'] = new JwtHelper($settings);
    }
}
