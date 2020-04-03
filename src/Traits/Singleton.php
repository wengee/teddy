<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-04-03 09:19:37 +0800
 */

namespace Teddy\Traits;

trait Singleton
{
    protected static $instances = [];

    public static function instance(): self
    {
        $className = get_called_class();
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className;
        }

        return self::$instances[$className];
    }

    protected function pushInstance($instance): void
    {
        $className = get_class($instance);
        self::$instances[$className] = $instance;
    }
}
