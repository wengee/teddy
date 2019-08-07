<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:01:34 +0800
 */

namespace Teddy\Validation\Validators;

class Required extends ValidatorRuleBase
{
    protected $message = ':label不能为空';

    protected function validate($value, array $data, callable $next)
    {
        if ($value === null ||
            (is_string($value) && strlen($value) === 0) ||
            (is_array($value) && count($value) === 0)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
