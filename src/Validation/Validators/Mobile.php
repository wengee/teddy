<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:11:45 +0800
 */
namespace Teddy\Validation\Validators;

class Mobile extends ValidatorRuleBase
{
    const REGEX = '/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$/';

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
