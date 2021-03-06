<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-12 11:02:05 +0800
 */

namespace Teddy\Facades;

/**
 * @method static string getName()
 * @method static string getBasePath()
 * @method static string getRuntimePath()
 */
class App extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'app';
    }
}
