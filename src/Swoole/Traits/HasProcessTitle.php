<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-14 10:43:50 +0800
 */
namespace Teddy\Swoole\Traits;

trait HasProcessTitle
{
    public function setProcessTitle($title)
    {
        if (PHP_OS === 'Darwin') {
            return;
        }
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($title);
        } elseif (function_exists('\swoole_set_process_name')) {
            \swoole_set_process_name($title);
        }
    }
}
