<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:12:38 +0800
 */
namespace Teddy\Validation\Validators;

class AlphaNum extends ValidatorRuleBase
{
    protected $message = ':label只能是字母和数字';

    protected function validate($value, array $data, callable $next)
    {
        if (!ctype_alnum($value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
