<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 10:26:07 +0800
 */
namespace Teddy\Traits;

trait Singleton
{
    protected static $instances = [];

    public static function instance()
    {
        $className = \get_called_class();
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className;
        }

        return self::$instances[$className];
    }

    protected function pushInstance($instance)
    {
        $className = \get_class($instance);
        self::$instances[$className] = $instance;
    }
}
