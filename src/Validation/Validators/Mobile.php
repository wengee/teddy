<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:11:45 +0800
 */
namespace Teddy\Validation\Validators;

class Mobile extends ValidatorRuleBase
{
    const REGEX = '/^1(([3][0-9])|([4][5-9])|([5][0-3,5-9])|([6][5,6])|([7][0-8])|([8][0-9])|([9][1,8,9]))[0-9]{8}$/';

    protected $message = ':label不是一个合法的手机号码';

    protected function validate($value, array $data, callable $next)
    {
        $value = trim(strval($value));
        if (!preg_match(self::REGEX, $value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
