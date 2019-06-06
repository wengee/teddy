<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:12:18 +0800
 */
namespace Teddy\Validation\Validators;

class Email extends ValidatorRuleBase
{
    protected $message = ':label不是有效的邮箱格式';

    protected function validate($value, array $data, callable $next)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
