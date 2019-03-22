<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:48:49 +0800
 */
namespace Teddy\Validation\Validators;

class Alpha extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        if (preg_match('/[^[:alpha:]]/imu', $value)) {
            $this->error('Field :label must contain only letters', $options);
        }
    }
}
