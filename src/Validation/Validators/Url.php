<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation\Validators;

class Url extends ValidatorRuleBase
{
    protected $message = ':label不是一个合法的URL';

    protected function validate($value, array $data, callable $next)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
