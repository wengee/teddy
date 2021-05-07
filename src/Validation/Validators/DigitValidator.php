<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 16:48:29 +0800
 */

namespace Teddy\Validation\Validators;

class DigitValidator extends Validator
{
    protected $message = ':label只能包含数字';

    protected function validate($value, array $data, callable $next)
    {
        if (!is_int($value) && !ctype_digit($value)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
