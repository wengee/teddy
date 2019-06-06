<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:11:26 +0800
 */
namespace Teddy\Validation\Validators;

class Url extends ValidatorRuleBase
{
    protected $message = ':label不是一个合法的URL';

    protected function validate($value, array $data, callable $next)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
