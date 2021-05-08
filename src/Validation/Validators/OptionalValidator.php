<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-08 16:00:07 +0800
 */

namespace Teddy\Validation\Validators;

use Illuminate\Support\Arr;

class OptionalValidator extends Validator
{
    protected function validate($value, array $data, callable $next)
    {
        $fieldName = $this->field->getName();
        if ($fieldName && !Arr::has($data, $fieldName)) {
            return null;
        }

        if (null === $value
            || (is_string($value) && 0 === strlen($value))
            || (is_array($value) && 0 === count($value))) {
            return $value;
        }

        return $next($value, $data);
    }
}
