<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-06-27 17:36:10 +0800
 */

namespace Teddy\Validation\Validators;

class IntegerValidator extends Validator
{
    protected $message = ':label必须为整数';

    protected function validate($value, array $data, callable $next)
    {
        if (!is_int($value) && !ctype_digit((string) $value)) {
            $this->throwError();
        }

        $value = intval($value);

        return $next($value, $data);
    }
}
