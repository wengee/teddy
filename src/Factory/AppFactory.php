<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-02-11 21:00:41 +0800
 */

namespace Teddy\Factory;

use Teddy\Scf\App as ScfApp;
use Teddy\Swoole\App as SwooleApp;
use Teddy\Utils\Runtime;

class AppFactory
{
    public static function create(string $basePath, string $envFile = '.env')
    {
        $runtime = Runtime::get();
        if ($runtime === 'scf') {
            return self::createScfApp($basePath, $envFile);
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
}
