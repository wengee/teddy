<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-07-14 15:33:11 +0800
 */

namespace Teddy\Factory;

use Teddy\Application;

class AppFactory
{
    public static function create(string $basePath, string $envFile = '.env')
    {
        return new Application($basePath, $envFile);
    }
}
