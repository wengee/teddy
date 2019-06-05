<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:01:19 +0800
 */
namespace Teddy\Validation\Validators;

class Double extends ValidatorBase
{
    public function validate($value, array $data, callable $next)
    {
        return $next(floatval($value), $data);
    }
}
