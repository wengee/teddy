<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 17:55:32 +0800
 */

namespace Teddy\Traits;

trait Singleton
{
    protected static $instances = [];

    public static function instance()
    {
        $className = get_called_class();
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className;
        }

        return self::$instances[$className];
    }

    protected function pushInstance($instance)
    {
        $className = get_class($instance);
        self::$instances[$className] = $instance;
    }
}
