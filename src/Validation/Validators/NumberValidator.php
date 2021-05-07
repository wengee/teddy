<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 16:32:34 +0800
 */

namespace Teddy\Validation\Validators;

class NumberValidator extends Validator
{
    protected $message = ':label不是有效的数字格式';

    protected function validate($value, array $data, callable $next)
    {
        $value = trim($value);
        if (!preg_match('/^-?\\d+\\.?\\d*$/', $value)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
