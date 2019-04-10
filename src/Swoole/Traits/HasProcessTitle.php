<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-10 18:17:07 +0800
 */
namespace Teddy\Swoole\Traits;

trait HasProcessTitle
{
    public function setProcessTitle($title)
    {
        if (PHP_OS === 'Darwin') {
            return;
        }

        $prefix = '';
        if (method_exists($this, 'getName')) {
            $prefix = $this->getName() . ': ';
        }

        if (function_exists('swoole_set_process_name')) {
            swoole_set_process_name($prefix . $title);
        } elseif (function_exists('cli_set_process_title')) {
            cli_set_process_title($prefix . $title);
        }
    }
}
