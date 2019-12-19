<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-19 16:28:03 +0800
 */

namespace Teddy\Factory;

use Teddy\App;
use Teddy\Scf\App as ScfApp;

class AppFactory
{
    public static function create(string $basePath, string $envFile = '.env')
    {
        if (defined('IN_SCF') && IN_SCF) {
            return self::createScfApp($basePath, $envFile);
        } else {
            return App::createSwooleApp($basePath, $envFile);
        }
    }

    public static function createScfApp(string $basePath, string $envFile = '.env')
    {
        return new ScfApp($basePath, $envFile);
    }

    public static function createSwooleApp(string $basePath, string $envFile = '.env')
    {
        return new App($basePath, $envFile);
    }
}
