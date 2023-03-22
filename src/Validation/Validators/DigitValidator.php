<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:30:26 +0800
 */

namespace Teddy\Validation\Validators;

class DigitValidator extends Validator
{
    protected string $message = ':label只能包含数字';

    public function validate($value, array $data, callable $next)
    {
        if (!is_int($value) && !ctype_digit($value)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
