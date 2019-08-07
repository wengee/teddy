<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:00:27 +0800
 */

namespace Teddy\Validation\Validators;

class Callback extends ValidatorRuleBase
{
    protected $func;

    public function __construct(callable $func)
    {
        $this->func = $func;
    }

    protected function validate($value, array $data, callable $next)
    {
        $value = call_user_func($this->func, $value, $data);
        return $next($value, $data);
    }
}
