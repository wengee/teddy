<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 14:55:38 +0800
 */
namespace Teddy\Validation\Validators;

class Double extends ValidatorBase
{
    public function validate($value, array $data)
    {
        return floatval($value);
    }
}
