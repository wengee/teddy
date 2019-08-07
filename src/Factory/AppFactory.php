<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 11:45:07 +0800
 */

namespace Teddy\Factory;

use Teddy\App;
use Teddy\CallableResolver;

class AppFactory
{
    public static function create(string $basePath = '')
    {
        return new App($basePath);
    }
}
