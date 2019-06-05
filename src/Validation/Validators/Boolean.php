<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 15:18:58 +0800
 */
namespace Teddy\Validation\Validators;

class Boolean extends ValidatorBase
{
    public function validate($value, array $data)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
