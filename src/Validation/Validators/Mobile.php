<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:58:18 +0800
 */
namespace Teddy\Validation\Validators;

class Mobile extends ValidatorBase
{
    const REGEX = '/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$/';

    public function validate($value, array $options = [])
    {
        if (!preg_match(static::REGEX, $value)) {
            $this->error('Field :label must be a mobile number.', $options);
        }
    }
}
