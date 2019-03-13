<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-14 15:17:22 +0800
 */
namespace Teddy\Swoole;

use Swoole\Coroutine;

class Utils
{
    public static function coroutineId()
    {
        return Coroutine::getuid();
    }
}
