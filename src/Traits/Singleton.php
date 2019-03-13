<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-18 15:17:52 +0800
 */
namespace Teddy\Traits;

trait Singleton
{
    protected static $instances = [];

    public static function instance()
    {
        $className = \get_called_class();
        return isset(static::$instances[$className]) ? static::$instances[$className] : new $className;
    }

    protected function pushInstance($instance)
    {
        $className = \get_class($instance);
        static::$instances[$className] = $instance;
    }
}
