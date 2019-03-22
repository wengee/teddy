<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:54:29 +0800
 */
namespace Teddy\Validation\Validators;

class Email extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->error('Field :label must be an email address', $options);
        }
    }
}
