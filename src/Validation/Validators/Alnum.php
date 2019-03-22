<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:48:53 +0800
 */
namespace Teddy\Validation\Validators;

class Alnum extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        if (!ctype_alnum($value)) {
            $this->error('Field :label must contain only letters and numbers', $options);
        }
    }
}
