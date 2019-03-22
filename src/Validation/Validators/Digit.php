<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:53:51 +0800
 */
namespace Teddy\Validation\Validators;

class Digit extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        if (!is_int($value) && !ctype_digit($value)) {
            $this->error('Field :label must be numeric', $options);
        }
    }
}
