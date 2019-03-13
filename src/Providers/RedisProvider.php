<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-07 09:54:20 +0800
 */
namespace Teddy\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Teddy\Redis\Client as Redis;
use Teddy\Swoole\Redis\Client as SwooleRedis;

class RedisProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $settings = $container['settings']->get('redis');
        if ($settings) {
            $settings = (array) $settings;

            if (defined('IN_SWOOLE') && IN_SWOOLE) {
                $container['redis'] = new SwooleRedis($settings);
            } else {
                $container['redis'] = new Redis($settings);
            }
        }
    }
}
