<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-30 17:08:20 +0800
 */

namespace Teddy\Utils;

use Phar;

class FileSystem
{
    public static function getContents($paths, string $file, bool $first = true)
    {
        $ret   = '';
        $paths = is_array($paths) ? $paths : [$paths];
        foreach ($paths as $path) {
            $filepath = self::joinPath($path, $file);
            if (is_file($filepath)) {
                $ret = file_get_contents($filepath);

                if ($first) {
                    return $ret;
                }
            }
        }

        return $ret;
    }

    public static function xcopy(string $source, string $dest, int $permissions = 0755): void
    {
        if (is_link($source)) {
            symlink(readlink($source), $dest);
        } elseif (is_file($source)) {
            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, $permissions, true);
            }

            copy($source, $dest);
        } elseif (is_dir($source)) {
            if (!is_dir($dest)) {
                mkdir($dest, $permissions, true);
            }

            $dh = opendir($source);
            while (false !== ($entry = readdir($dh))) {
                if ('.' === $entry || '..' === $entry) {
                    continue;
                }

                static::xcopy($source.DIRECTORY_SEPARATOR.$entry, $dest.DIRECTORY_SEPARATOR.$entry, $permissions);
            }
            closedir($dh);
        }
    }

    public static function joinPath(string $basePath, ?string ...$args): string
    {
        if (!$args) {
            return $basePath;
        }

        $basePath = rtrim($basePath, '\\/').DIRECTORY_SEPARATOR;
        $paths    = array_filter(array_map(function ($arg) {
            return ($arg && is_string($arg)) ? trim($arg, '\\/') : '';
        }, $args), 'strlen');

        return $basePath.implode(DIRECTORY_SEPARATOR, $paths);
    }

    public static function humanFilesize(int $bytes, int $decimals = 3): string
    {
        $factor = floor((strlen(strval($bytes)) - 1) / 3);
        if ($factor > 0) {
            $sz = 'KMGT';
        }

        return sprintf("%.{$decimals}f", $bytes / 1024 ** $factor).@$sz[$factor - 1].'B';
    }

    public static function getRuntimePath(): ?string
    {
        $pharPath = Phar::running(false);
        if ($pharPath) {
            return dirname($pharPath);
        }

        return null;
    }
}
