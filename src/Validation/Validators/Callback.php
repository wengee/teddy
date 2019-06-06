<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 16:12:36 +0800
 */
namespace Teddy\Validation\Validators;

use Exception;

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
