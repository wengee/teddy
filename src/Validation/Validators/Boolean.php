<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 17:56:42 +0800
 */
namespace Teddy\Validation\Validators;

class Boolean extends ValidatorBase
{
    public function validate($value, array $data, callable $next)
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        return $next($value, $data);
    }
}
