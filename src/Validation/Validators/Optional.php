<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:01:23 +0800
 */

namespace Teddy\Validation\Validators;

class Optional extends ValidatorRuleBase
{
    protected function validate($value, array $data, callable $next)
    {
        if ($value === null ||
            (is_string($value) && strlen($value) === 0) ||
            (is_array($value) && count($value) === 0)) {
            return $value;
        }

        return $next($value, $data);
    }
}
