<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-21 12:55:48 +0800
 */

namespace Teddy\Utils;

class FileSystem
{
    public static function getContents($paths, string $file, bool $first = true)
    {
        $ret = '';
        $paths = is_array($paths) ? $paths : [$paths];
        foreach ($paths as $path) {
            $filepath = path_join($path, $file);
            if (is_file($filepath)) {
                $ret = file_get_contents($filepath);

                if ($first) {
                    return $ret;
                }
            }
        }

        return $ret;
    }
}
