<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-11 10:40:13 +0800
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
}
