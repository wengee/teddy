<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation\Validators;

class Required extends ValidatorRuleBase
{
    protected $message = ':label不能为空';

    protected function validate($value, array $data, callable $next)
    {
        if ($value === null ||
            (is_string($value) && strlen($value) === 0) ||
            (is_array($value) && count($value) === 0)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
