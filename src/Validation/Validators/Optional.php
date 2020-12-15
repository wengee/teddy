<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-12-15 17:20:44 +0800
 */

namespace Teddy\Validation\Validators;

use Illuminate\Support\Arr;

class Optional extends ValidatorRuleBase
{
    protected function validate($value, array $data, callable $next)
    {
        if (!Arr::has($data, $this->field)) {
            return null;
        }

        if (null === $value ||
            (is_string($value) && 0 === strlen($value)) ||
            (is_array($value) && 0 === count($value))) {
            return $value;
        }

        return $next($value, $data);
    }
}
