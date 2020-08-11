<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-11 17:11:09 +0800
 */

namespace Teddy\Facades;

/**
 * @method getBasePath(): string
 * @method getRuntimePath(): string
 */
class App extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'app';
    }
}
