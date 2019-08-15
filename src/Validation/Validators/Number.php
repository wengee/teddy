<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
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
