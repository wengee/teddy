<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 23:31:37 +0800
 */

namespace Teddy\Validation\Validators;

class OptionalValidator extends Validator
{
    protected function validate($value, array $data, callable $next)
    {
        if (null === $value
            || (is_string($value) && 0 === strlen($value))
            || (is_array($value) && 0 === count($value))) {
            return $value;
        }

        return $next($value, $data);
    }
}
