<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-10-09 10:41:35 +0800
 */

namespace Teddy;

class Utils
{
    public static function setProcessTitle(string $title, ?string $prefix = null): void
    {
        if (PHP_OS === 'Darwin') {
            return;
        }

        if ($prefix) {
            $title = $prefix . ': ' . $title;
        }

        if (function_exists('swoole_set_process_name')) {
            swoole_set_process_name($title);
        } elseif (function_exists('cli_set_process_title')) {
            cli_set_process_title($title);
        }
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

            $dir = dir($source);
            while (false !== ($entry = $dir->read())) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                static::xcopy("{$source}/{$entry}", "{$dest}/{$entry}", $permissions);
            }
            $dir->close();
        }
    }

    public static function clearDir(string $src): void
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                $full = $src . DIRECTORY_SEPARATOR . $file;
                if (is_dir($full)) {
                    self::clearDir($full);
                } else {
                    unlink($full);
                }
            }
        }

        closedir($dir);
    }

    public static function humanFilesize(int $bytes, int $decimals = 3)
    {
        $factor = floor((strlen(strval($bytes)) - 1) / 3);
        if ($factor > 0) {
            $sz = 'KMGT';
        }
        return sprintf("%.{$decimals}f", $bytes / 1024 ** $factor) . @$sz[$factor - 1] . 'B';
    }
}
