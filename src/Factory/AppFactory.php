<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-20 17:02:15 +0800
 */

namespace Teddy\Factory;

use Teddy\App;
use Teddy\Utils\Runtime;
use Teddy\Scf\App as ScfApp;
use Teddy\Swoole\App as SwooleApp;

class AppFactory
{
    public static function create(string $basePath, string $envFile = '.env')
    {
        $runtime = Runtime::get();
        if ($runtime === 'scf') {
            return self::createScfApp($basePath, $envFile);
        } elseif ($runtime === 'fpm' || $runtime === 'normal') {
            return self::createNormalApp($basePath, $envFile);
        } else {
            return self::createSwooleApp($basePath, $envFile);
        }
    }

    public static function createScfApp(string $basePath, string $envFile = '.env')
    {
        return new ScfApp($basePath, $envFile);
    }

    public static function createSwooleApp(string $basePath, string $envFile = '.env')
    {
        return new SwooleApp($basePath, $envFile);
    }

    public static function createNormalApp(string $basePath, string $envFile = '.env')
    {
        return new App($basePath, $envFile);
    }
}
