<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-15 14:13:06 +0800
 */
namespace Teddy;

use Exception;

class Utils
{
    public static function callWithCatchException(callable $func, array $params = [])
    {
        try {
            call_user_func_array($func, $params);
        } catch (Exception $e) {
            log_exception($e);
            return false;
        }

        return true;
    }

    public static function xcopy(string $source, string $dest, int $permissions = 0755)
    {
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        if (is_file($source)) {
            return copy($source, $dest);
        }

        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        $dir = dir($source);
        while (false !== ($entry = $dir->read())) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            self::xcopy("{$source}/{$entry}", "{$dest}/{$entry}", $permissions);
        }

        $dir->close();
        return true;
    }

    public static function clearDir(string $src)
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
}
