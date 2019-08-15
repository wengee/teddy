<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation\Validators;

class AlphaNum extends ValidatorRuleBase
{
    protected $message = ':label只能是字母和数字';

    protected function validate($value, array $data, callable $next)
    {
        if (!ctype_alnum($value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
