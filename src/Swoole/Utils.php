<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:02:47 +0800
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
