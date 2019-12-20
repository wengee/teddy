<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-20 10:11:39 +0800
 */

namespace Teddy\Factory;

use Teddy\Scf\App as ScfApp;
use Teddy\Swoole\App as SwooleApp;

class AppFactory
{
    public static function create(string $basePath, string $envFile = '.env')
    {
        $runtime = defined('TEDDY_RUNTIME') ? TEDDY_RUNTIME : self::fetchRuntime();
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

    protected static function fetchRuntime(): string
    {
        $runtime = 'swoole';
        $scf = getenv('TENCENTCLOUD_RUNENV');
        if ($scf && strtolower($scf) === 'scf') {
            $runtime = 'scf';
        }

        return $runtime;
    }
}
