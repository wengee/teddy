<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

class AlphaNumValidator extends Validator
{
    protected $message = ':label只能是字母和数字';

    protected function validate($value, array $data, callable $next)
    {
        if (!ctype_alnum($value)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
