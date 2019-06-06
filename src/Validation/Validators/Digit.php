<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:12:22 +0800
 */
namespace Teddy\Validation\Validators;

class Digit extends ValidatorRuleBase
{
    protected $message = ':label只能包含数字';

    protected function validate($value, array $data, callable $next)
    {
        if (!is_int($value) && !ctype_digit($value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
