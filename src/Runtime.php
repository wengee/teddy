<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-20 12:06:26 +0800
 */

namespace App;

class Runtime
{
    protected static $runtime;

    public static function get(): string
    {
        if (!isset(self::$runtime)) {
            if (defined('TEDDY_RUNTIME')) {
                self::$runtime = strtolower(TEDDY_RUNTIME);
            } else {
                self::$runtime = 'swoole';

                $scf = getenv('TENCENTCLOUD_RUNENV');
                if ($scf && strtolower($scf) === 'scf') {
                    self::$runtime = 'scf';
                }
            }
        }

        return self::$runtime;
    }

    public static function set(string $runtime): void
    {
        self::$runtime = strtolower($runtime);
    }
}
