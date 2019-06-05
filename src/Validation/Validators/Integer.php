<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 14:55:14 +0800
 */
namespace Teddy\Validation\Validators;

class Integer extends ValidatorBase
{
    public function validate($value, array $data)
    {
        return intval($value);
    }
}
