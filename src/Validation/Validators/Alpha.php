<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:12:40 +0800
 */
namespace Teddy\Validation\Validators;

class Alpha extends ValidatorRuleBase
{
    protected $message = ':label只能是字母';

    protected function validate($value, array $data, callable $next)
    {
        if (preg_match('/[^[:alpha:]]/imu', $value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
