<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:11:43 +0800
 */
namespace Teddy\Validation\Validators;

class Number extends ValidatorRuleBase
{
    protected $message = ':label不是有效的数字格式';

    protected function validate($value, array $data, callable $next)
    {
        $value = trim($value);
        if (!preg_match("/^-?\d+\.?\d*$/", $value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
