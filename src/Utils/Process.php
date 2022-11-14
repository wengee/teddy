<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 21:02:20 +0800
 */

namespace Teddy\Utils;

class Process
{
    public static function setTitle(string $title, ?string $prefix = null): void
    {
        if (PHP_OS === 'Darwin') {
            return;
        }

        $prefix = $prefix ?: app()->getName();
        $title  = $prefix.': '.$title;

        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($title);
        } elseif (function_exists('swoole_set_process_name')) {
            \swoole_set_process_name($title);
        }
    }
}
