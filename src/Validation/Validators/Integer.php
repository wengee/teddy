<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:02:37 +0800
 */
namespace Teddy\Validation\Validators;

class Integer extends ValidatorBase
{
    public function validate($value, array $data, callable $next)
    {
        return $next(intval($value), $data);
    }
}
