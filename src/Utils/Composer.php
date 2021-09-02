<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-05 15:30:53 +0800
 */

namespace Teddy\Utils;

use Composer\Autoload\ClassLoader;
use RuntimeException;

class Composer
{
    private static $loader;

    public static function getLoader(): ClassLoader
    {
        if (self::$loader) {
            return self::$loader;
        }

        return self::findLoader();
    }

    private static function findLoader(): ClassLoader
    {
        $composerClass = '';
        foreach (get_declared_classes() as $declaredClass) {
            if (0 === strpos($declaredClass, 'ComposerAutoloaderInit') && method_exists($declaredClass, 'getLoader')) {
                $composerClass = $declaredClass;

                break;
            }
        }

        if (!$composerClass) {
            throw new RuntimeException('Composer loader not found.');
        }

        self::$loader = $composerClass::getLoader();

        return self::$loader;
    }
}
