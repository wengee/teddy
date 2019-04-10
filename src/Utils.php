<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-10 18:39:51 +0800
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
}
