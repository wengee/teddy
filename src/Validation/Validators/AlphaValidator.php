<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 16:53:22 +0800
 */

namespace Teddy\Validation\Validators;

class AlphaValidator extends Validator
{
    protected $message = ':label只能是字母';

    protected function validate($value, array $data, callable $next)
    {
        if (preg_match('/[^[:alpha:]]/imu', $value)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
