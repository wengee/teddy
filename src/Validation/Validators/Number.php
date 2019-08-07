<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:01:20 +0800
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
